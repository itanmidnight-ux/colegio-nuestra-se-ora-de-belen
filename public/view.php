<?php
require_once "../config.php";

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$id = intval($_GET['id']);

$stmt = $conn->prepare("SELECT id, titulo, director, publicado_en, archivo_pdf FROM periodicos WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if (!$result || $result->num_rows === 0) {
    header("Location: index.php?error=notfound");
    exit;
}
$periodico_principal = $result->fetch_assoc();

$sql_todos = "SELECT id, titulo, director, publicado_en FROM periodicos ORDER BY publicado_en DESC";
$result_todos = $conn->query($sql_todos);
$periodicos_array = [];
if ($result_todos && $result_todos->num_rows > 0) {
    while ($row = $result_todos->fetch_assoc()) {
        $periodicos_array[] = $row;
    }
}

$months = [
    "enero", "febrero", "marzo", "abril", "mayo", "junio",
    "julio", "agosto", "septiembre", "octubre", "noviembre", "diciembre"
];
$ts = filemtime(__FILE__);
$last_mod = date('j', $ts) . " de " . $months[(int)date('n', $ts) - 1] . " de " . date('Y', $ts) . " a las " . date('H:i:s', $ts);
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title><?php echo htmlspecialchars($periodico_principal['titulo']); ?> - ECO BELÉN</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="style.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&family=Merriweather:wght@300;700&display=swap" rel="stylesheet">
</head>
<body class="pdf-viewer-page">
  <a class="corner-logo" href="index.php" aria-label="Ir al inicio">
    <img src="escudo.jpeg" alt="Escudo Institucional">
  </a>

  <header class="public-header">
    <div class="top-bar">Institución Educativa Nuestra Señora de Belén · Cúcuta</div>
    <div class="header-inner">
      <div class="brand-text">
        <span class="brand-name">Institución Educativa Nuestra Señora de Belén</span>
        <span class="brand-sub">ECO BELÉN · Lectura en línea</span>
      </div>
      <nav class="main-nav">
        <a href="index.php">Inicio</a>
        <a href="periodicos.php">Periódicos</a>
        <a href="contacto.php">Contacto</a>
      </nav>
    </div>
  </header>

  <main class="public-content" id="main-content">
    <aside class="side-panel periodicos-panel" id="periodicosPanel">
      <button class="toggle-button" id="togglePeriodicosBtn" aria-label="Mostrar u ocultar ediciones">&lt;</button>
      <h3>Ediciones Anteriores</h3>
      <div class="listado">
        <?php
        if (count($periodicos_array) > 0) {
            foreach ($periodicos_array as $row) {
                $isActive = ($row['id'] == $periodico_principal['id']) ? 'active' : '';
                echo "<div class='list-item {$isActive}'>
                        <a href='view.php?id={$row['id']}' title='Ver {$row['titulo']}'>
                            <strong>{$row['titulo']}</strong>
                            <span>Fecha: {$row['publicado_en']} | Dir: {$row['director']}</span>
                        </a>
                      </div>";
            }
        } else {
            echo "<p>No hay periódicos disponibles aún.</p>";
        }
        ?>
      </div>
    </aside>

    <section class="main-periodico-display">
      <h2><?php echo htmlspecialchars($periodico_principal['titulo']); ?></h2>
      <p>Fecha: <?php echo htmlspecialchars($periodico_principal['publicado_en']); ?> | Dir: <?php echo htmlspecialchars($periodico_principal['director']); ?></p>

      <div class="pdf-container">
        <iframe src="../uploads/<?php echo htmlspecialchars($periodico_principal['archivo_pdf']); ?>" frameborder="0"></iframe>
      </div>
    </section>

    <aside class="side-panel comments-panel" id="commentsPanel">
      <button class="toggle-button" id="toggleCommentsBtn" aria-label="Mostrar u ocultar comentarios">&gt;</button>
      <h3>Comentarios</h3>
      <form id="commentForm">
          <input type="hidden" name="id_periodico" value="<?php echo $periodico_principal['id']; ?>">
          <input type="text" name="usuario" placeholder="Tu nombre" required>
          <textarea name="comentario" placeholder="Escribe tu comentario..." required></textarea>
          <button type="submit">Enviar</button>
      </form>
      <div id="commentsList">
          <?php
          $cstmt = $conn->prepare("SELECT usuario_nombre, comentario, creado_en FROM comentarios WHERE periodico_id = ? ORDER BY creado_en DESC");
          $cstmt->bind_param("i", $id);
          $cstmt->execute();
          $cresult = $cstmt->get_result();

          if ($cresult && $cresult->num_rows > 0) {
              while ($c = $cresult->fetch_assoc()) {
                  echo "<div class='comment'>
                          <strong>" . htmlspecialchars($c['usuario_nombre']) . "</strong>
                          <p>" . htmlspecialchars($c['comentario']) . "</p>
                          <span>{$c['creado_en']}</span>
                        </div>";
              }
          } else {
              echo "<p>No hay comentarios aún. ¡Sé el primero en opinar!</p>";
          }
          $cstmt->close();
          ?>
      </div>
    </aside>
  </main>

  <footer class="footer" id="contacto">
    <div class="footer-inner">
      <div class="footer-col">
        <h4>Contáctanos</h4>
        <ul class="footer-list">
          <li>
            <span class="footer-icon" aria-hidden="true">
              <svg viewBox="0 0 24 24"><path d="M3 10l9-5 9 5"></path><path d="M5 10v8h14v-8"></path><path d="M9 18v-4h6v4"></path></svg>
            </span>
            Institución Educativa Colegio Nuestra Señora de Belén
          </li>
          <li>
            <span class="footer-icon" aria-hidden="true">
              <svg viewBox="0 0 24 24"><path d="M12 21s-6-5.3-6-10a6 6 0 1 1 12 0c0 4.7-6 10-6 10z"></path><circle cx="12" cy="11" r="2.5"></circle></svg>
            </span>
            Cúcuta - Norte Santander
          </li>
          <li>
            <span class="footer-icon" aria-hidden="true">
              <svg viewBox="0 0 24 24"><circle cx="12" cy="8" r="3.5"></circle><path d="M4 20c1.5-3 4-5 8-5s6.5 2 8 5"></path></svg>
            </span>
            Rectora: CARLOS LUIS VILLAMIZAR RAMIREZ
          </li>
        </ul>
        <div class="footer-social">
          <a href="https://www.facebook.com/" target="_blank" rel="noreferrer" aria-label="Facebook">f</a>
          <a href="https://wa.me/" target="_blank" rel="noreferrer" aria-label="WhatsApp">w</a>
          <a href="https://www.instagram.com/" target="_blank" rel="noreferrer" aria-label="Instagram">i</a>
        </div>
      </div>
      <div class="footer-col">
        <h4>Atención al Público</h4>
        <ul class="footer-list">
          <li>
            <span class="footer-icon" aria-hidden="true">
              <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"></circle><path d="M12 7v5l3 2"></path></svg>
            </span>
            Horario de atención:
          </li>
          <li>
            <span class="footer-icon" aria-hidden="true">
              <svg viewBox="0 0 24 24"><path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6 19.8 19.8 0 0 1-3.1-8.6A2 2 0 0 1 4 1h3a2 2 0 0 1 2 1.7c.1.9.3 1.8.6 2.7a2 2 0 0 1-.5 2.1L8 8a16 16 0 0 0 6 6l.8-.9a2 2 0 0 1 2.1-.5c.9.3 1.8.5 2.7.6a2 2 0 0 1 1.7 2z"></path></svg>
            </span>
            6075920077
          </li>
          <li>
            <span class="footer-icon" aria-hidden="true">
              <svg viewBox="0 0 24 24"><rect x="3" y="5" width="18" height="14" rx="2"></rect><path d="M3 7l9 6 9-6"></path></svg>
            </span>
            colnubelen@semcucuta.gov.co
          </li>
        </ul>
        <div class="footer-clock">
          <div class="clock-box" id="footerClock"></div>
          <div class="footer-meta">Última modificación: <?php echo $last_mod; ?></div>
        </div>
      </div>
      <div class="footer-col">
        <h4>Enviar correo con:</h4>
        <div class="footer-links">
          <a href="https://mail.google.com/" target="_blank" rel="noreferrer">Gmail</a>
          <a href="https://outlook.live.com/" target="_blank" rel="noreferrer">Outlook / Hotmail</a>
        </div>
        <div class="footer-links">
          <a href="https://www.webcolegios.com/" target="_blank" rel="noreferrer">[webcolegios]</a>
          <a href="https://www.colnubelen.edu.co/" target="_blank" rel="noreferrer">[Mapa de Sitio]</a>
        </div>
      </div>
    </div>
    <div class="footer-bottom">© 2026 - Desarrollada por webcolegios | Institución Educativa Nuestra Señora de Belén</div>
  </footer>

  <script src="script.js"></script>
</body>
</html>
