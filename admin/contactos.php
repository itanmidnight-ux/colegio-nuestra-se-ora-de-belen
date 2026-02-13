<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.html");
    exit;
}

require_once "../config.php";

function ensureContactosColumns(mysqli $conn, array $columns): void
{
    foreach ($columns as $column => $definition) {
        $safeColumn = $conn->real_escape_string($column);
        $existsResult = $conn->query("SHOW COLUMNS FROM contactos LIKE '{$safeColumn}'");
        if ($existsResult && $existsResult->num_rows === 0) {
            $conn->query("ALTER TABLE contactos ADD COLUMN {$column} {$definition}");
        }
        if ($existsResult) {
            $existsResult->free();
        }
    }
}

$create = "CREATE TABLE IF NOT EXISTS contactos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(120) NOT NULL,
    email VARCHAR(180) NOT NULL,
    tipo VARCHAR(60) NOT NULL,
    mensaje TEXT NOT NULL,
    solicita_contacto TINYINT(1) NOT NULL DEFAULT 0,
    grado VARCHAR(80) DEFAULT NULL,
    urgente TINYINT(1) NOT NULL DEFAULT 0,
    nombre_contacto VARCHAR(120) DEFAULT NULL,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
$conn->query($create);

ensureContactosColumns($conn, [
    "solicita_contacto" => "TINYINT(1) NOT NULL DEFAULT 0",
    "grado" => "VARCHAR(80) DEFAULT NULL",
    "urgente" => "TINYINT(1) NOT NULL DEFAULT 0",
    "nombre_contacto" => "VARCHAR(120) DEFAULT NULL"
]);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete_selected') {
    $ids = $_POST['ids'] ?? [];
    if (!is_array($ids)) {
        $ids = [];
    }

    $ids = array_values(array_filter(array_map('intval', $ids), function ($id) {
        return $id > 0;
    }));

    if (!empty($ids)) {
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $types = str_repeat('i', count($ids));
        $stmtDelete = $conn->prepare("DELETE FROM contactos WHERE id IN ({$placeholders})");
        if ($stmtDelete) {
            $stmtDelete->bind_param($types, ...$ids);
            $stmtDelete->execute();
            $stmtDelete->close();
            $_SESSION['contactos_flash'] = count($ids) . " mensaje(s) eliminado(s) correctamente.";
        } else {
            $_SESSION['contactos_flash'] = "No se pudieron eliminar los mensajes seleccionados.";
        }
    } else {
        $_SESSION['contactos_flash'] = "Debes seleccionar al menos un mensaje para eliminar.";
    }

    $query = http_build_query([
        'filtro' => $_POST['filtro'] ?? 'todos',
        'q' => $_POST['q'] ?? ''
    ]);
    header("Location: contactos.php" . (!empty($query) ? "?{$query}" : ''));
    exit;
}

$filtro = $_GET['filtro'] ?? 'todos';
$busqueda = trim($_GET['q'] ?? '');
$flash = $_SESSION['contactos_flash'] ?? '';
unset($_SESSION['contactos_flash']);

$where = [];
$params = [];
$types = '';

if ($filtro === 'urgente') {
    $where[] = 'urgente = 1';
} elseif ($filtro === 'contacto') {
    $where[] = 'solicita_contacto = 1';
}

if ($busqueda !== '') {
    $where[] = '(nombre LIKE ? OR email LIKE ? OR mensaje LIKE ? OR grado LIKE ?)';
    $like = "%{$busqueda}%";
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
    $types .= 'ssss';
}

$sql = "SELECT id, nombre, email, tipo, mensaje, solicita_contacto, grado, urgente, nombre_contacto, creado_en
        FROM contactos";
if (!empty($where)) {
    $sql .= " WHERE " . implode(' AND ', $where);
}
$sql .= " ORDER BY urgente DESC, creado_en DESC";

$stmt = $conn->prepare($sql);
if ($stmt && !empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$mensajes = [];
if ($stmt) {
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $mensajes[] = $row;
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Mensajes de contacto - Admin</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">
  <link href="style.css" rel="stylesheet">
</head>
<body>
<a class="corner-logo" href="dashboard.php" aria-label="Ir al panel">
    <img src="escudo.png" alt="Escudo Institucional">
</a>
<header class="public-header">
    <div class="header-title-wrapper">
        <h1>💬 Bandeja de mensajes de contacto</h1>
    </div>
    <nav class="header-actions" aria-label="Navegación del panel admin">
        <a href="dashboard.php" class="btn-view">Panel</a>
        <a href="secciones.php" class="btn-view">Secciones</a>
        <a href="encuestas.php" class="btn-view">Encuestas</a>
        <a href="logout.php" class="btn-view">Cerrar sesión</a>
    </nav>
</header>

<main class="mensajes-app">
    <aside class="mensajes-sidebar">
      <h2>Filtros</h2>
      <div class="chip-filters">
        <a class="chip <?php echo $filtro === 'todos' ? 'active' : ''; ?>" href="contactos.php?filtro=todos">Todos</a>
        <a class="chip <?php echo $filtro === 'contacto' ? 'active' : ''; ?>" href="contactos.php?filtro=contacto">Con opción contacto</a>
        <a class="chip urgent <?php echo $filtro === 'urgente' ? 'active' : ''; ?>" href="contactos.php?filtro=urgente">Urgentes</a>
      </div>
      <form method="get" class="busqueda-form">
        <input type="hidden" name="filtro" value="<?php echo htmlspecialchars($filtro); ?>">
        <label for="q">Buscar</label>
        <input id="q" name="q" type="text" value="<?php echo htmlspecialchars($busqueda); ?>" placeholder="Nombre, grado, correo o mensaje">
        <button type="submit" class="btn-view" style="width:100%; margin-top:10px;">Aplicar búsqueda</button>
      </form>
      <div class="stats-box">
        <p><strong>Total encontrados:</strong> <?php echo count($mensajes); ?></p>
        <p><strong>Filtro actual:</strong> <?php echo htmlspecialchars(ucfirst($filtro)); ?></p>
      </div>
    </aside>

    <section class="mensajes-list">
      <?php if (!empty($flash)): ?>
        <div class="flash-message"><?php echo htmlspecialchars($flash); ?></div>
      <?php endif; ?>

      <form method="post" id="deleteMessagesForm" onsubmit="return confirm('¿Seguro que deseas eliminar los mensajes seleccionados? Esta acción no se puede deshacer.');">
        <input type="hidden" name="action" value="delete_selected">
        <input type="hidden" name="filtro" value="<?php echo htmlspecialchars($filtro); ?>">
        <input type="hidden" name="q" value="<?php echo htmlspecialchars($busqueda); ?>">

        <div class="bulk-actions">
          <label class="check-all-wrap">
            <input type="checkbox" id="selectAllMessages">
            Seleccionar todo
          </label>
          <button type="submit" class="btn-delete bulk-delete-btn">🗑️ Eliminar seleccionados</button>
        </div>

      <?php if (empty($mensajes)): ?>
        <article class="mensaje-card empty">
          <h3>No hay mensajes para este filtro</h3>
          <p>Prueba cambiando los filtros o la búsqueda.</p>
        </article>
      <?php else: ?>
        <?php foreach ($mensajes as $mensaje): ?>
          <article class="mensaje-card <?php echo (int)$mensaje['urgente'] === 1 ? 'is-urgent' : ''; ?>">
            <div class="mensaje-select-row">
              <label>
                <input type="checkbox" class="mensaje-check" name="ids[]" value="<?php echo (int)$mensaje['id']; ?>">
                Seleccionar mensaje
              </label>
            </div>

            <div class="mensaje-head">
              <div>
                <h3><?php echo htmlspecialchars($mensaje['nombre']); ?></h3>
                <p><?php echo htmlspecialchars($mensaje['email']); ?> · <?php echo htmlspecialchars($mensaje['tipo']); ?></p>
              </div>
              <div class="badges">
                <?php if ((int)$mensaje['solicita_contacto'] === 1): ?>
                  <span class="badge badge-contacto">Contacto activado</span>
                <?php endif; ?>
                <?php if ((int)$mensaje['urgente'] === 1): ?>
                  <span class="badge badge-urgente">Urgente</span>
                <?php endif; ?>
              </div>
            </div>

            <p class="mensaje-body"><?php echo nl2br(htmlspecialchars($mensaje['mensaje'])); ?></p>

            <div class="mensaje-meta">
              <span><strong>Fecha:</strong> <?php echo htmlspecialchars($mensaje['creado_en']); ?></span>
              <span><strong>Nombre de contacto:</strong> <?php echo htmlspecialchars($mensaje['nombre_contacto'] ?: 'No aplica'); ?></span>
              <span><strong>Grado/Curso:</strong> <?php echo htmlspecialchars($mensaje['grado'] ?: 'No aplica'); ?></span>
            </div>
          </article>
        <?php endforeach; ?>
      <?php endif; ?>
      </form>
    </section>
</main>

<script>
const selectAll = document.getElementById('selectAllMessages');
const checks = Array.from(document.querySelectorAll('.mensaje-check'));

if (selectAll) {
  selectAll.addEventListener('change', function () {
    checks.forEach(function (check) {
      check.checked = selectAll.checked;
    });
  });
}

checks.forEach(function (check) {
  check.addEventListener('change', function () {
    if (!selectAll) {
      return;
    }
    selectAll.checked = checks.length > 0 && checks.every(function (item) { return item.checked; });
  });
});
</script>
</body>
</html>
