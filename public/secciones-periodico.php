<?php
require_once "../config.php";
$conn->query("CREATE TABLE IF NOT EXISTS secciones_periodico (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(180) NOT NULL,
    descripcion TEXT,
    contenido LONGTEXT,
    imagen VARCHAR(255),
    bloques_extra LONGTEXT,
    orden_visual INT DEFAULT 0,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    actualizado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$conn->query("ALTER TABLE secciones_periodico ADD COLUMN IF NOT EXISTS bloques_extra LONGTEXT AFTER imagen");

$secciones = [];
$res = $conn->query("SELECT titulo, descripcion, contenido, imagen, bloques_extra FROM secciones_periodico ORDER BY orden_visual ASC, creado_en DESC");
if ($res) while ($r = $res->fetch_assoc()) $secciones[] = $r;

function normalizar_url($url)
{
    if (preg_match('/^(https?:\/\/|\.\.\/uploads\/|\/uploads\/)/i', $url)) {
        return $url;
    }
    return '../uploads/' . ltrim($url, '/');
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Secciones del periódico - ECO BELÉN</title>
  <link href="style.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&family=Merriweather:wght@300;700&display=swap" rel="stylesheet">
</head>
<body>
  <a class="corner-logos" href="index.php" aria-label="Ir al inicio">
    <img src="escudo.jpeg" alt="Escudo Institucional">
    <img src="logo-ecobelen.jpg" alt="Logo ECO Belén">
  </a>
  <header class="public-header">
    <div class="top-bar">Institución Educativa Nuestra Señora de Belén · Cúcuta</div>
    <div class="header-inner">
      <div class="brand-text">
        <span class="brand-name">Institución Educativa Nuestra Señora de Belén</span>
        <span class="brand-sub">Secciones editoriales ECO BELÉN</span>
      </div>
      <nav class="main-nav">
        <a href="index.php">Inicio</a>
        <a href="periodicos.php">Periódicos</a>
        <a href="secciones-periodico.php">Secciones</a>
        <a href="contacto.php">Contacto</a>
      </nav>
    </div>
  </header>
  <main class="periodicos-page">
    <div class="section-header">
      <h1>Secciones del periódico escolar</h1>
      <p>Este contenido se actualiza desde el panel administrativo.</p>
    </div>
    <div class="periodicos-list sections-grid">
      <?php if (count($secciones) > 0): ?>
        <?php foreach ($secciones as $sec): ?>
          <?php
          $bloques = json_decode($sec['bloques_extra'] ?? '[]', true);
          if (!is_array($bloques)) {
              $bloques = [];
          }
          ?>
          <article class="periodico-card anim-card section-card">
            <?php if (!empty($sec['imagen'])): ?>
              <div class="periodico-thumb section-thumb">
                <img src="../uploads/<?= htmlspecialchars($sec['imagen']) ?>" alt="Imagen sección <?= htmlspecialchars($sec['titulo']) ?>" class="section-image-main">
              </div>
            <?php endif; ?>

            <h2 class="section-title"><?= htmlspecialchars($sec['titulo']) ?></h2>
            <p class="section-description"><?= htmlspecialchars($sec['descripcion']) ?></p>
            <p><?= nl2br(htmlspecialchars($sec['contenido'])) ?></p>

            <?php if (count($bloques) > 0): ?>
              <div class="section-extra-content">
                <?php foreach ($bloques as $bloque): ?>
                  <?php
                  $tipo = $bloque['tipo'] ?? '';
                  $valor = trim($bloque['valor'] ?? '');
                  if ($valor === '') continue;
                  ?>

                  <?php if ($tipo === 'texto'): ?>
                    <p class="section-extra-text"><?= nl2br(htmlspecialchars($valor)) ?></p>
                  <?php elseif ($tipo === 'imagen'): ?>
                    <figure class="section-extra-media">
                      <img src="<?= htmlspecialchars(normalizar_url($valor)) ?>" alt="Imagen adicional de <?= htmlspecialchars($sec['titulo']) ?>">
                    </figure>
                  <?php elseif ($tipo === 'video'): ?>
                    <div class="section-video-wrap">
                      <iframe src="<?= htmlspecialchars($valor) ?>" title="Video de <?= htmlspecialchars($sec['titulo']) ?>" allowfullscreen loading="lazy"></iframe>
                    </div>
                  <?php endif; ?>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>
          </article>
        <?php endforeach; ?>
      <?php else: ?>
        <p>No hay secciones creadas aún.</p>
      <?php endif; ?>
    </div>
  </main>
  <script src="script.js"></script>
</body>
</html>
