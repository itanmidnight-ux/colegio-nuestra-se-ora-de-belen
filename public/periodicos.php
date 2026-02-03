<?php
require_once "../config.php";

$sql = "SELECT id, titulo, director, publicado_en, archivo_pdf FROM periodicos ORDER BY publicado_en DESC";
$result = $conn->query($sql);
$periodicos_array = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
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
  <title>Periódicos - ECO BELÉN</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="style.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&family=Merriweather:wght@300;700&display=swap" rel="stylesheet">
</head>
<body>
  <a class="corner-logo" href="index.php" aria-label="Ir al inicio">
    <img src="escudo.png" alt="Escudo Institucional">
  </a>

  <header class="public-header" id="inicio">
    <div class="top-bar">Institución Educativa Nuestra Señora de Belén · Cúcuta</div>
    <div class="header-inner">
      <div class="brand-text">
        <span class="brand-name">Institución Educativa Nuestra Señora de Belén</span>
        <span class="brand-sub">Listado oficial de periódicos</span>
      </div>
      <nav class="main-nav">
        <a href="index.php">Inicio</a>
        <a href="contacto.php">Contacto</a>
      </nav>
    </div>
  </header>

  <main>
    <section class="periodicos-page" aria-labelledby="periodicos-title">
      <div class="section-header">
        <h2 id="periodicos-title">Periódicos escolares</h2>
        <p>Selecciona una edición para lectura en línea o descarga inmediata.</p>
      </div>

      <div class="periodicos-list">
        <?php
        if (count($periodicos_array) > 0) {
            $delay = 0;
            foreach ($periodicos_array as $row) {
                $delay += 0.05;
                $delayStyle = number_format($delay, 2);
                echo "<article class='periodico-card anim-card' style='--delay: {$delayStyle}s'>
                        <a class='card-link' href='view.php?id={$row['id']}'>Abrir</a>
                        <div class='periodico-thumb'>
                          <object data='../uploads/{$row['archivo_pdf']}#page=1&zoom=70' type='application/pdf'>
                            <iframe src='../uploads/{$row['archivo_pdf']}#page=1&zoom=70'></iframe>
                          </object>
                        </div>
                        <div>
                          <h3>{$row['titulo']}</h3>
                          <p>Fecha: {$row['publicado_en']}</p>
                          <p>Dir: {$row['director']}</p>
                        </div>
                        <div class='periodico-buttons'>
                          <a class='btn-primary' href='../uploads/{$row['archivo_pdf']}' download>Descargar</a>
                          <a class='btn-primary' href='view.php?id={$row['id']}'>Lectura en línea</a>
                        </div>
                      </article>";
            }
        } else {
            echo "<p>No hay periódicos disponibles aún.</p>";
        }
        ?>
      </div>
    </section>
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
