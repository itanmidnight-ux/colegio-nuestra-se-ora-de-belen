<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.html");
    exit;
}

require_once "../config.php";

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

$schemaUpdates = [
    "ALTER TABLE contactos ADD COLUMN solicita_contacto TINYINT(1) NOT NULL DEFAULT 0",
    "ALTER TABLE contactos ADD COLUMN grado VARCHAR(80) DEFAULT NULL",
    "ALTER TABLE contactos ADD COLUMN urgente TINYINT(1) NOT NULL DEFAULT 0",
    "ALTER TABLE contactos ADD COLUMN nombre_contacto VARCHAR(120) DEFAULT NULL"
];
foreach ($schemaUpdates as $update) {
    @$conn->query($update);
}

$filtro = $_GET['filtro'] ?? 'todos';
$busqueda = trim($_GET['q'] ?? '');

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
    <a href="dashboard.php" class="btn-view" style="position:absolute; top:20px; right:170px;">Panel</a>
    <a href="logout.php" class="btn-view" style="position:absolute; top:20px; right:20px;">Cerrar sesión</a>
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
      <?php if (empty($mensajes)): ?>
        <article class="mensaje-card empty">
          <h3>No hay mensajes para este filtro</h3>
          <p>Prueba cambiando los filtros o la búsqueda.</p>
        </article>
      <?php else: ?>
        <?php foreach ($mensajes as $mensaje): ?>
          <article class="mensaje-card <?php echo (int)$mensaje['urgente'] === 1 ? 'is-urgent' : ''; ?>">
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
    </section>
</main>

</body>
</html>
