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
<body data-survey-page="periodicos">
  <a class="corner-logos" href="index.php" aria-label="Ir al inicio">
    <img src="escudo.jpeg" alt="Escudo Institucional">
    <img src="logo-ecobelen.jpg" alt="Logo ECO Belén">
  </a>

  <header class="public-header" id="inicio">
    <div class="top-bar">Institución Educativa Nuestra Señora de Belén · Cúcuta</div>
    <div class="header-inner">
      <div class="brand-text">
        <span class="brand-name">Institución Educativa Nuestra Señora de Belén</span>
        <span class="brand-sub">Archivo digital de periódicos escolares</span>
      </div>
      <nav class="main-nav">
        <a href="index.php">Inicio</a>
        <a href="#archivo">Archivo</a>
        <a href="secciones-periodico.php">Secciones</a>
        <a href="contacto.php">Contacto</a>
      </nav>
    </div>
  </header>

  <main>
    <section class="periodicos-hero" aria-labelledby="periodicos-hero-title">
      <div class="periodicos-intro">
        <div>
          <p class="hero-kicker">ECO BELÉN · Voces que inspiran</p>
          <h1 id="periodicos-hero-title">Periódicos escolares con identidad Colnubelen</h1>
          <p class="hero-lead">Descubre las historias, investigaciones y proyectos que nacen en nuestras aulas. Cada edición refleja la creatividad, la investigación y el compromiso social de nuestra comunidad educativa.</p>
        </div>
        <div class="periodicos-stats">
          <div class="metric-card">
            <h3>Publicación</h3>
            <p>Ediciones oficiales con enfoque académico y cultural.</p>
          </div>
          <div class="metric-card">
            <h3>Equipo editorial</h3>
            <p>Docentes y estudiantes lideran el proceso de creación.</p>
          </div>
          <div class="metric-card">
            <h3>Acceso abierto</h3>
            <p>Lectura en línea y descargas disponibles.</p>
          </div>
        </div>
      </div>
    </section>

    <section class="periodicos-page" id="archivo" aria-labelledby="periodicos-title">
      <div class="section-header">
        <h2 id="periodicos-title">Periódicos escolares</h2>
        <p>Selecciona una edición para lectura en línea o descarga inmediata.</p>
      </div>

      <div class="periodicos-guides">
        <article class="guide-card anim-card" style="--delay: 0.05s">
          <h4>Lineamientos editoriales</h4>
          <p>Promovemos contenidos con sentido humano, respeto por la diversidad y rigor investigativo.</p>
        </article>
        <article class="guide-card anim-card" style="--delay: 0.1s">
          <h4>Integración pedagógica</h4>
          <p>Los periódicos fortalecen las áreas de lenguaje, ciencias sociales y tecnología.</p>
        </article>
        <article class="guide-card anim-card" style="--delay: 0.15s">
          <h4>Memoria institucional</h4>
          <p>Cada edición registra los proyectos más importantes de la institución.</p>
        </article>
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
                        <div class='periodico-thumb periodico-thumb-full'>
                          <iframe src='../uploads/{$row['archivo_pdf']}#page=1&zoom=page-fit&toolbar=0&navpanes=0&scrollbar=0' title='Vista previa de {$row['titulo']}'></iframe>
                        </div>
                        <div>
                          <h3>{$row['titulo']}</h3>
                          <p>Fecha: {$row['publicado_en']}</p>
                          <p>Dir: {$row['director']}</p>
                        </div>
                        <div class='periodico-buttons'>
                          <a class='btn-primary survey-download-link' href='../uploads/{$row['archivo_pdf']}' download>Descargar</a>
                          <a class='btn-primary' href='view.php?id={$row['id']}'>Lectura en línea</a>
                        </div>
                      </article>";
            }
        } else {
            echo "<p>No hay periódicos disponibles aún.</p>";
        }
        ?>
      </div>

      <div class="periodicos-cta">
        <div>
          <h3>¿Quieres participar en ECO BELÉN?</h3>
          <p>Comparte tu propuesta con el equipo editorial y construyamos juntos nuevas historias.</p>
        </div>
        <a class="btn-outline" href="contacto.php">Enviar propuesta</a>
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
            Cúcuta - Norte de Santander
          </li>
          <li>
            <span class="footer-icon" aria-hidden="true">
              <svg viewBox="0 0 24 24"><circle cx="12" cy="8" r="3.5"></circle><path d="M4 20c1.5-3 4-5 8-5s6.5 2 8 5"></path></svg>
            </span>
            Rector: Carlos Luis Villamizar Ramírez
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
            Horario de atención: Jornada mañana, tarde y única
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
            <button class="btn-outline" type="button" data-open-modal="correoModal">colnubelen@semcucuta.gov.co</button>
          </li>
        </ul>
        <div class="footer-clock">
          <div class="clock-box" id="footerClock"></div>
          <div class="footer-meta">Última modificación: <?php echo $last_mod; ?></div>
        </div>
      </div>
      <div class="footer-col">
        <h4>Enlaces útiles</h4>
        <div class="footer-links">
          <a href="https://www.webcolegios.com/" target="_blank" rel="noreferrer">[webcolegios]</a>
          <a href="https://www.colnubelen.edu.co/" target="_blank" rel="noreferrer">[Mapa de Sitio]</a>
        </div>
        <div class="footer-links">
          <a href="https://www.colnubelen.edu.co/sedes.php" target="_blank" rel="noreferrer">Sedes</a>
          <a href="https://www.colnubelen.edu.co/pqr.php" target="_blank" rel="noreferrer">PQRS</a>
        </div>
      </div>
    </div>
    <div class="footer-bottom">© 2026 - Desarrollada por webcolegios | Institución Educativa Nuestra Señora de Belén</div>
  </footer>

  <div class="footer-modal" id="correoModal" aria-hidden="true">
    <div class="footer-modal-content">
      <h3>📩 Enviar correo con:</h3>
      <div class="footer-modal-actions">
        <a href="https://mail.google.com/mail/?view=cm&fs=1&to=colnubelen@semcucuta.gov.co" target="_blank" rel="noreferrer">Gmail</a>
        <a href="https://outlook.live.com/owa/?path=/mail/action/compose&to=colnubelen@semcucuta.gov.co" target="_blank" rel="noreferrer">Outlook / Hotmail</a>
      </div>
      <button class="footer-modal-close" type="button" data-close-modal>Cerrar</button>
    </div>
  </div>

  <script src="script.js"></script>
</body>
</html>
