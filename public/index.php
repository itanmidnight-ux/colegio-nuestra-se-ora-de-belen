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
  <title>Institución Educativa Nuestra Señora de Belén</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="style.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&family=Merriweather:wght@300;700&display=swap" rel="stylesheet">
</head>
<body>
  <a class="corner-logo" href="index.php" aria-label="Ir al inicio">
    <img src="escudo.jpeg" alt="Escudo Institucional">
  </a>

  <header class="public-header" id="inicio">
    <div class="top-bar">Institución Educativa Nuestra Señora de Belén · Cúcuta</div>
    <div class="header-inner">
      <div class="brand-text">
        <span class="brand-name">Institución Educativa Nuestra Señora de Belén</span>
        <span class="brand-sub">ECO BELÉN · Comunidad educativa y cultural</span>
      </div>
      <nav class="main-nav">
        <a href="#inicio">Inicio</a>
        <a href="#institucion">Institución</a>
        <a href="#sedes">Sedes</a>
        <a href="periodicos.php">Periódicos</a>
        <a href="contacto.php">Contacto</a>
      </nav>
    </div>
  </header>

  <main>
    <section class="hero" aria-labelledby="hero-title">
      <div class="hero-grid">
        <div class="hero-text">
          <p class="hero-kicker">Institución Pública · Mixta · Cúcuta</p>
          <h1 id="hero-title">Bienvenidos a la Institución Educativa Nuestra Señora de Belén</h1>
          <p class="hero-lead">Somos una comunidad educativa que impulsa la ciencia, la convivencia y el servicio. Acompañamos a nuestros estudiantes desde la primera infancia hasta la media técnica para transformar el entorno con valores y excelencia.</p>
          <div class="hero-actions">
            <a class="btn-primary" href="periodicos.php">Explorar periódicos</a>
            <a class="btn-outline" href="contacto.php">Agenda una visita</a>
          </div>
          <div class="hero-metrics">
            <div class="metric-card">
              <h3>Naturaleza</h3>
              <p>Pública</p>
            </div>
            <div class="metric-card">
              <h3>Población</h3>
              <p>Mixta</p>
            </div>
            <div class="metric-card">
              <h3>Jornada</h3>
              <p>Mañana · Tarde · Única</p>
            </div>
          </div>
        </div>
        <div class="hero-media">
          <div class="hero-card anim-card" style="--delay: 0.1s">
            <h2>Identidad institucional</h2>
            <p>Formamos niños y jóvenes con principios éticos, sociales y culturales, apoyados en la ciencia y la tecnología, para impulsar su crecimiento personal y social.</p>
            <div class="tag-list">
              <span>Excelencia</span>
              <span>Innovación</span>
              <span>Convivencia</span>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section class="spotlight-section" id="institucion" aria-labelledby="institucion-title">
      <div class="section-header">
        <h2 id="institucion-title">Nuestra institución</h2>
        <p>Inspirados en la identidad Colnubelen, fortalecemos la formación académica y humana con una visión futurista.</p>
      </div>
      <div class="spotlight-grid">
        <article class="spotlight-card anim-card" style="--delay: 0.05s">
          <h3>Sede principal</h3>
          <ul class="info-list">
            <li><strong>Dirección:</strong> Calle 26 No. 27-60, Barrio Belén.</li>
            <li><strong>Municipio:</strong> Cúcuta - Norte de Santander.</li>
            <li><strong>Niveles:</strong> Primera infancia, básica primaria, básica secundaria, media académica, media técnica y aceleración del aprendizaje.</li>
            <li><strong>Rector:</strong> Carlos Luis Villamizar Ramírez.</li>
          </ul>
        </article>
        <article class="spotlight-card anim-card" style="--delay: 0.1s">
          <h3>Horizonte institucional</h3>
          <div class="spotlight-highlight">
            <p>Ser líderes en formación académica y técnica, con valores humanos sólidos y crecimiento cualitativo de la comunidad educativa, apoyados en ciencia, cultura y tecnología.</p>
          </div>
          <div class="panel-tags">
            <span>Calidad</span>
            <span>Servicio</span>
            <span>Identidad</span>
          </div>
        </article>
        <article class="spotlight-card anim-card" style="--delay: 0.15s">
          <h3>Símbolos institucionales</h3>
          <p>Conoce los elementos que representan nuestra historia y visión en la comunidad educativa Colnubelen.</p>
          <a class="btn-outline" href="https://www.colnubelen.edu.co/simbolos.php" target="_blank" rel="noreferrer">Ver símbolos</a>
        </article>
      </div>
    </section>

    <section class="sedes-section" id="sedes" aria-labelledby="sedes-title">
      <div class="section-header">
        <h2 id="sedes-title">Sedes educativas</h2>
        <p>Presencia estratégica para acompañar a nuestras familias en diferentes sectores de Cúcuta.</p>
      </div>
      <div class="sedes-grid">
        <article class="sede-card anim-card" style="--delay: 0.05s">
          <h4>Sede principal</h4>
          <p>Calle 26 No. 27-60, Barrio Belén.</p>
        </article>
        <article class="sede-card anim-card" style="--delay: 0.1s">
          <h4>Belén No. 23</h4>
          <p>Calle 25 #27-40.</p>
        </article>
        <article class="sede-card anim-card" style="--delay: 0.15s">
          <h4>Belén No. 21</h4>
          <p>Calle 25 #27-10.</p>
        </article>
        <article class="sede-card anim-card" style="--delay: 0.2s">
          <h4>Rudesindo Soto</h4>
          <p>AV 30 #17-22.</p>
        </article>
      </div>
    </section>

    <section class="services-section" aria-labelledby="services-title">
      <div class="section-header">
        <h2 id="services-title">Canales institucionales</h2>
        <p>Accede a los servicios que acompañan la vida escolar y el bienestar de la comunidad.</p>
      </div>
      <div class="services-grid">
        <div class="service-card anim-card" style="--delay: 0.05s">
          <img src="https://www.colnubelen.edu.co/images/botones/c1.png" alt="Cuadro de honor">
          <span>Cuadro de honor</span>
        </div>
        <div class="service-card anim-card" style="--delay: 0.1s">
          <img src="https://www.colnubelen.edu.co/images/botones/e1.png" alt="Egresados">
          <span>Egresados</span>
        </div>
        <div class="service-card anim-card" style="--delay: 0.15s">
          <img src="https://www.colnubelen.edu.co/images/botones/p1.png" alt="PQRS">
          <span>PQRS</span>
        </div>
      </div>
    </section>

    <section class="avisos-section" aria-labelledby="avisos-title">
      <div class="section-header">
        <h2 id="avisos-title">Avisos importantes</h2>
        <p>Mensajes de interés para estudiantes, familias y comunidad educativa.</p>
      </div>
      <div class="avisos-card anim-card" style="--delay: 0.05s">
        <p>Consulta los comunicados vigentes y mantente informado sobre actividades académicas y culturales.</p>
        <div class="contact-map" style="margin-top: 16px;">
          <iframe src="https://www.webcolegios.com/mensaje_principal.php?idcolegio=8" title="Mensajes de interés"></iframe>
        </div>
      </div>
    </section>

    <section class="periodicos-section" id="periodicos" aria-labelledby="periodicos-title">
      <div class="section-header">
        <h2 id="periodicos-title">Último periódico publicado</h2>
        <p>Acceso rápido a la edición más reciente de ECO BELÉN.</p>
      </div>

      <div class="periodicos-ultima">
        <?php
        if (!empty($periodicos_array)) {
            $ultimo = $periodicos_array[0];
            echo "<article class='periodico-hero anim-card' style='--delay: 0.1s'>
                    <div>
                      <p class='periodico-kicker'>Última edición</p>
                      <h3>{$ultimo['titulo']}</h3>
                      <p>Fecha: {$ultimo['publicado_en']} · Dir: {$ultimo['director']}</p>
                    </div>
                    <div class='periodico-actions'>
                      <a href='view.php?id={$ultimo['id']}' class='btn-primary'>Lectura en línea</a>
                      <a href='periodicos.php' class='btn-outline'>Ver todos</a>
                    </div>
                  </article>";
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
