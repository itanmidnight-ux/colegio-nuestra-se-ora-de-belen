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
  <title>ECO BELÉN - Periódicos Escolares</title>
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
        <span class="brand-sub">ECO BELÉN · Periódicos escolares</span>
      </div>
      <nav class="main-nav">
        <a href="#inicio">Inicio</a>
        <a href="#institucion">Institución</a>
        <a href="#mision-vision">Misión y visión</a>
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
          <h1 id="hero-title">Institución Educativa Nuestra Señora de Belén</h1>
          <p class="hero-lead">Formamos estudiantes con sentido humano, vocación de servicio y competencias para transformar su entorno con responsabilidad, ciencia y valores.</p>
          <div class="hero-actions">
            <a class="btn-primary" href="periodicos.php">Ver periódicos</a>
            <a class="btn-outline" href="contacto.php">Contáctanos</a>
          </div>
        </div>
        <div class="hero-media">
          <div class="hero-card anim-card" style="--delay: 0.1s">
            <h2>Identidad institucional</h2>
            <p>Una comunidad educativa que promueve la ciencia, la paz y el progreso como pilares de su formación integral.</p>
            <div class="tag-list">
              <span>Excelencia</span>
              <span>Convivencia</span>
              <span>Innovación</span>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section class="panel-section" id="institucion" aria-labelledby="institucion-title">
      <div class="section-header">
        <h2 id="institucion-title">Nuestra Institución</h2>
        <p>Datos generales de la sede principal y características institucionales.</p>
      </div>
      <div class="panel-grid">
        <article class="panel-card anim-card" style="--delay: 0.05s">
          <h3>Sede Principal</h3>
          <ul class="info-list">
            <li><strong>Dirección:</strong> Calle 26 No. 27-60, Barrio Belén.</li>
            <li><strong>Municipio:</strong> Cúcuta - Norte de Santander.</li>
            <li><strong>Naturaleza:</strong> Pública.</li>
            <li><strong>Población:</strong> Mixto.</li>
            <li><strong>Niveles:</strong> Primera infancia, básica primaria, básica secundaria, media académica, media técnica y aceleración del aprendizaje.</li>
            <li><strong>Jornada de trabajo:</strong> Mañana - Tarde - Única.</li>
            <li><strong>Rectora:</strong> CARLOS LUIS VILLAMIZAR RAMIREZ.</li>
          </ul>
        </article>
        <article class="panel-card anim-card" style="--delay: 0.1s">
          <div class="panel-illustration" aria-hidden="true"></div>
          <div>
            <h3>Atención al público y sedes</h3>
            <p>Información de contacto y ubicación de las sedes principales con canales oficiales de comunicación.</p>
            <div class="panel-tags">
              <span>Secretaría</span>
              <span>Atención al público</span>
              <span>Sedes</span>
            </div>
          </div>
        </article>
      </div>
    </section>

    <section class="attention-section" aria-labelledby="attention-title">
      <div class="section-header">
        <h2 id="attention-title">Atención al Público</h2>
        <p>Información institucional organizada para facilitar la comunicación.</p>
      </div>
      <div class="attention-grid">
        <article class="attention-card card-blue anim-card" style="--delay: 0.05s">
          <h3>Líneas de atención</h3>
          <ul class="attention-list">
            <li>Teléfono: 6075920077</li>
            <li>Correo: colnubelen@semcucuta.gov.co</li>
            <li>Secretaría: Calle 26 No. 27-60</li>
          </ul>
        </article>
        <article class="attention-card card-light anim-card" style="--delay: 0.1s">
          <h3>Institución Educativa</h3>
          <p>Comprometida con la calidad académica, la convivencia y la formación integral de nuestros estudiantes.</p>
          <div class="panel-tags">
            <span>#SomosColnubelen</span>
            <span>Identidad</span>
          </div>
        </article>
        <article class="attention-card card-red anim-card" style="--delay: 0.15s">
          <h3>Ubicaciones</h3>
          <ul class="attention-list">
            <li><span>Sede Principal:</span> Calle 26 No. 27-60</li>
            <li><span>Belén No. 23:</span> Calle 25 #27-40</li>
            <li><span>Belén No. 21:</span> Calle 25 #27-10</li>
            <li><span>Rudesindo Soto:</span> AV 30 #17-22</li>
          </ul>
        </article>
      </div>
    </section>

    <section class="mv-section" id="mision-vision" aria-labelledby="mv-title">
      <div class="section-header">
        <h2 id="mv-title">Misión y Visión</h2>
        <p>Horizonte institucional que orienta nuestros procesos formativos.</p>
      </div>
      <div class="mv-grid">
        <article class="mv-card anim-card" style="--delay: 0.05s">
          <h3>Misión</h3>
          <p>Formar niños y jóvenes con principios éticos, sociales y culturales, apoyados en la ciencia y la tecnología, para impulsar su crecimiento personal y social.</p>
        </article>
        <article class="mv-card anim-card" style="--delay: 0.1s">
          <h3>Visión</h3>
          <p>Ser líderes en formación académica y técnica, con valores humanos sólidos y crecimiento cualitativo de la comunidad educativa, apoyados en ciencia, cultura y tecnología.</p>
        </article>
      </div>
    </section>

    <section class="services-section" aria-labelledby="services-title">
      <div class="section-header">
        <h2 id="services-title">Canales institucionales</h2>
        <p>Accesos rápidos a servicios y procesos académicos.</p>
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
        <h2 id="avisos-title">Avisos Importantes</h2>
        <p>Mensajes de interés para estudiantes, familias y comunidad educativa.</p>
      </div>
      <div class="avisos-card anim-card" style="--delay: 0.05s">Mensajes de interés</div>
    </section>

    <section class="periodicos-section" id="periodicos" aria-labelledby="periodicos-title">
      <div class="section-header">
        <h2 id="periodicos-title">Último periódico publicado</h2>
        <p>Acceso rápido a la edición más reciente.</p>
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
