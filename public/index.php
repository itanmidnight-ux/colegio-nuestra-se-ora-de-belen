<?php
require_once "../config.php";
require_once "visit_tracker.php";
registerSectionVisit($conn, 'inicio');

$sql = "SELECT id, titulo, director, publicado_en, archivo_pdf FROM periodicos ORDER BY publicado_en DESC";
$result = $conn->query($sql);
$periodicos_array = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $periodicos_array[] = $row;
    }
}

$secciones_array = [];
$seccionesResult = $conn->query("SELECT id, titulo, descripcion, imagen, contenido FROM secciones_periodico ORDER BY orden_visual ASC, creado_en DESC LIMIT 6");
if ($seccionesResult && $seccionesResult->num_rows > 0) {
    while ($row = $seccionesResult->fetch_assoc()) {
        $secciones_array[] = $row;
    }
}

function portada_src($imagen)
{
    if (!$imagen) {
        return null;
    }

    if (preg_match('/^https?:\/\//i', $imagen) || strpos($imagen, '../uploads/') === 0 || strpos($imagen, 'uploads/') === 0) {
        return $imagen;
    }

    return "../uploads/" . ltrim($imagen, '/');
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
<body data-survey-page="home">
  <header class="public-header" id="inicio">
    <div class="top-bar">Institución Educativa Nuestra Señora de Belén · Cúcuta</div>
    <div class="header-inner">
      <a class="header-logos" href="index.php" aria-label="Ir al inicio">
        <img src="escudo.jpeg" alt="Escudo Institucional">
        <img src="logo-ecobelen.jpg" alt="Logo ECO Belén">
      </a>
      <div class="brand-text">
        <span class="brand-name">Institución Educativa Nuestra Señora de Belén</span>
        <span class="brand-sub">ECO BELÉN · Comunidad educativa y cultural</span>
      </div>
      <nav class="main-nav">
        <a href="#inicio">Inicio</a>
        <a href="#institucion">Institución</a>
        <a href="#secciones">Secciones</a>
        <a href="periodicos.php">Periódicos</a>
        <a href="secciones-periodico.php">Edición completa</a>
        <a href="contacto.php">Contacto</a>
      </nav>
    </div>
  </header>

  <main>
    <section class="hero" aria-labelledby="hero-title">
      <div class="hero-grid">
        <div class="hero-text">
          <p class="hero-kicker">Institución Pública · Mixta · Cúcuta</p>
          <h1 id="hero-title">Periódico escolar con identidad, ciencia y cultura</h1>
          <p class="hero-lead">ECO BELÉN es la vitrina periodística de nuestra comunidad educativa: una plataforma para narrar logros estudiantiles, iniciativas académicas, vida cultural, proyectos ambientales y voces juveniles con una estética editorial contemporánea.</p>
          <div class="hero-actions">
            <a class="btn-primary" href="periodicos.php">Explorar periódicos</a>
            <a class="btn-outline" href="secciones-periodico.php">Ver secciones</a>
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
            <p>Formamos niños y jóvenes con principios éticos, sociales y culturales, apoyados en la ciencia y la tecnología para transformar su entorno.</p>
            <div class="tag-list">
              <span>Excelencia</span>
              <span>Innovación</span>
              <span>Convivencia</span>
            </div>
          </div>
        </div>
      </div>
    </section>


    <section class="about-project" aria-labelledby="about-project-title">
      <div class="section-header">
        <h2 id="about-project-title">Proyecto periódico escolar ECO BELÉN</h2>
        <p>Una plataforma pública para mostrar, publicar y preservar las ediciones del periódico escolar.</p>
      </div>
      <div class="about-project-grid">
        <article class="about-project-card anim-card" style="--delay: 0.05s">
          <h3>Descripción del proyecto</h3>
          <p>ECO BELÉN nace como un proyecto pedagógico y comunicativo de la Institución Educativa Nuestra Señora de Belén para fortalecer la lectura crítica, la escritura argumentativa y el liderazgo estudiantil. Este portal reúne artículos, crónicas, entrevistas, columnas de opinión, muestras artísticas y reportajes sobre ciencia, deporte, cultura y convivencia escolar, permitiendo que cada edición del periódico sea visible para estudiantes, familias, egresados y comunidad en general.</p>
          <p>Además de publicar contenidos, la página funciona como archivo histórico del colegio: conserva las producciones editoriales por año, facilita la consulta de secciones temáticas y promueve una ciudadanía digital responsable al dar contexto, fuentes y lenguaje periodístico de calidad. Con esta iniciativa, el periódico deja de ser una publicación aislada para convertirse en un espacio vivo de participación, memoria y construcción colectiva.</p>
        </article>
        <article class="about-project-card about-project-highlights anim-card" style="--delay: 0.1s">
          <h3>Enfoque editorial y comunitario</h3>
          <ul class="info-list">
            <li><strong>Formación:</strong> fortalece competencias comunicativas y pensamiento crítico.</li>
            <li><strong>Participación:</strong> visibiliza la voz de estudiantes y docentes.</li>
            <li><strong>Memoria:</strong> organiza y conserva periódicos escolares en línea.</li>
            <li><strong>Proyección:</strong> conecta a la comunidad con procesos y logros institucionales.</li>
          </ul>
          <div class="panel-tags">
            <span>Crónicas escolares</span>
            <span>Investigación juvenil</span>
            <span>Arte y cultura</span>
            <span>Opinión estudiantil</span>
          </div>
        </article>
      </div>
    </section>

    <section class="spotlight-section" id="institucion" aria-labelledby="institucion-title">
      <div class="section-header">
        <h2 id="institucion-title">Nuestra institución</h2>
        <p>Inspirados en la identidad Colnubelen, fortalecemos la formación académica y humana con visión futurista.</p>
      </div>
      <div class="spotlight-grid">
        <article class="spotlight-card anim-card" style="--delay: 0.05s">
          <h3>Sede principal</h3>
          <ul class="info-list">
            <li><strong>Dirección:</strong> Calle 26 No. 27-60, Barrio Belén.</li>
            <li><strong>Municipio:</strong> Cúcuta - Norte de Santander.</li>
            <li><strong>Niveles:</strong> Primera infancia, básica primaria, secundaria, media académica y técnica.</li>
            <li><strong>Rector:</strong> Carlos Luis Villamizar Ramírez.</li>
          </ul>
        </article>
        <article class="spotlight-card anim-card" style="--delay: 0.1s">
          <h3>Horizonte institucional</h3>
          <div class="spotlight-highlight">
            <p>Ser líderes en formación académica y técnica, con valores humanos sólidos y crecimiento cualitativo de la comunidad educativa.</p>
          </div>
          <div class="panel-tags">
            <span>Calidad</span>
            <span>Servicio</span>
            <span>Identidad</span>
          </div>
        </article>
        <article class="spotlight-card anim-card" style="--delay: 0.15s">
          <h3>Símbolos institucionales</h3>
          <p>Conoce los elementos que representan nuestra historia y visión institucional.</p>
          <a class="btn-outline" href="https://www.colnubelen.edu.co/simbolos.php" target="_blank" rel="noreferrer">Ver símbolos</a>
        </article>
      </div>
    </section>

    <section class="news-layout-section" id="secciones" aria-labelledby="secciones-title">
      <div class="section-header">
        <h2 id="secciones-title">Secciones destacadas del periódico</h2>
        <p>Portada editorial en formato de bloques para una lectura rápida y visual.</p>
      </div>
      <div class="news-layout">
        <div class="news-main">
          <?php if (!empty($secciones_array)): ?>
            <?php $principal = $secciones_array[0]; ?>
            <article class="news-feature anim-card" style="--delay: 0.05s">
              <?php if (portada_src($principal['imagen'])): ?>
                <div class="news-media">
                  <img src="<?= htmlspecialchars(portada_src($principal['imagen'])) ?>" alt="Sección <?= htmlspecialchars($principal['titulo']) ?>">
                </div>
              <?php endif; ?>
              <div class="news-copy">
                <p class="periodico-kicker">Sección principal</p>
                <h3><?= htmlspecialchars($principal['titulo']) ?></h3>
                <p><?= htmlspecialchars($principal['descripcion'] ?: mb_substr(strip_tags($principal['contenido']), 0, 180) . '...') ?></p>
                <a class="btn-primary" href="secciones-periodico.php">Leer sección</a>
              </div>
            </article>
          <?php else: ?>
            <article class="news-feature anim-card"><p>No hay secciones creadas aún.</p></article>
          <?php endif; ?>

          <div class="news-subgrid">
            <?php foreach (array_slice($secciones_array, 1, 3) as $i => $sec): ?>
              <article class="news-tile anim-card" style="--delay: <?= number_format(0.1 + ($i * 0.05), 2) ?>s">
                <?php if (portada_src($sec['imagen'])): ?>
                  <img src="<?= htmlspecialchars(portada_src($sec['imagen'])) ?>" alt="<?= htmlspecialchars($sec['titulo']) ?>">
                <?php endif; ?>
                <h4><?= htmlspecialchars($sec['titulo']) ?></h4>
                <p><?= htmlspecialchars(mb_substr($sec['descripcion'] ?: strip_tags($sec['contenido']), 0, 100)) ?>...</p>
              </article>
            <?php endforeach; ?>
          </div>
        </div>

        <aside class="news-sidebar">
          <article class="news-side-card anim-card" style="--delay: 0.1s">
            <h4>Accesos rápidos</h4>
            <ul class="info-list">
              <li><a href="periodicos.php">Archivo de periódicos</a></li>
              <li><a href="secciones-periodico.php">Todas las secciones</a></li>
              <li><a href="contacto.php">Contacto institucional</a></li>
            </ul>
          </article>
          <article class="news-side-card anim-card" style="--delay: 0.15s">
            <h4>Canales institucionales</h4>
            <div class="panel-tags">
              <span>Cuadro de honor</span>
              <span>Egresados</span>
              <span>PQRS</span>
              <span>Convivencia</span>
            </div>
          </article>
        </aside>
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
      </div>
      <div class="footer-col">
        <h4>Atención al Público</h4>
        <ul class="footer-list">
          <li><span class="footer-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"></circle><path d="M12 7v5l3 2"></path></svg></span>Horario de atención: Jornada mañana, tarde y única</li>
          <li><span class="footer-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6 19.8 19.8 0 0 1-3.1-8.6A2 2 0 0 1 4 1h3a2 2 0 0 1 2 1.7c.1.9.3 1.8.6 2.7a2 2 0 0 1-.5 2.1L8 8a16 16 0 0 0 6 6l.8-.9a2 2 0 0 1 2.1-.5c.9.3 1.8.5 2.7.6a2 2 0 0 1 1.7 2z"></path></svg></span>6075920077</li>
          <li><span class="footer-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><rect x="3" y="5" width="18" height="14" rx="2"></rect><path d="M3 7l9 6 9-6"></path></svg></span><button class="btn-outline" type="button" data-open-modal="correoModal">colnubelen@semcucuta.gov.co</button></li>
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
