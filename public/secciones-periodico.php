<?php
require_once "../config.php";
$conn->query("CREATE TABLE IF NOT EXISTS secciones_periodico (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(180) NOT NULL,
    descripcion TEXT,
    contenido LONGTEXT,
    imagen VARCHAR(255),
    contenido_extra LONGTEXT,
    orden_visual INT DEFAULT 0,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    actualizado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$colCheck = $conn->query("SHOW COLUMNS FROM secciones_periodico LIKE 'contenido_extra'");
if ($colCheck && $colCheck->num_rows === 0) {
    $conn->query("ALTER TABLE secciones_periodico ADD COLUMN contenido_extra LONGTEXT AFTER imagen");
}

$secciones = [];
$res = $conn->query("SELECT titulo, descripcion, contenido, imagen, contenido_extra FROM secciones_periodico ORDER BY orden_visual ASC, creado_en DESC");
if ($res) {
    while ($r = $res->fetch_assoc()) {
        $secciones[] = $r;
    }
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

    <div class="periodicos-list secciones-list">
      <?php if (count($secciones) > 0): ?>
        <?php foreach ($secciones as $sec): ?>
          <?php
            $extra = json_decode($sec['contenido_extra'] ?? '[]', true);
            if (!is_array($extra)) {
                $extra = [];
            }
            usort($extra, function($a, $b){
                return intval($a['orden'] ?? 0) <=> intval($b['orden'] ?? 0);
            });
          ?>
          <article class="periodico-card anim-card seccion-card">
            <?php if (!empty($sec['imagen'])): ?>
              <div class="periodico-thumb seccion-thumb">
                <img src="../uploads/<?= htmlspecialchars($sec['imagen']) ?>" alt="Imagen sección <?= htmlspecialchars($sec['titulo']) ?>">
              </div>
            <?php endif; ?>

            <h2 class="seccion-title"><?= htmlspecialchars($sec['titulo']) ?></h2>
            <?php if (!empty($sec['descripcion'])): ?><p class="seccion-descripcion"><?= htmlspecialchars($sec['descripcion']) ?></p><?php endif; ?>
            <?php if (!empty($sec['contenido'])): ?><p><?= nl2br(htmlspecialchars($sec['contenido'])) ?></p><?php endif; ?>

            <?php if (!empty($extra)): ?>
              <div class="seccion-extra-content">
                <?php foreach ($extra as $bloque): ?>
                  <?php $tipo = $bloque['tipo'] ?? 'texto'; $valor = trim($bloque['valor'] ?? ''); if ($valor === '') continue; ?>
                  <?php if ($tipo === 'texto'): ?>
                    <p class="extra-texto"><?= nl2br(htmlspecialchars($valor)) ?></p>
                  <?php elseif ($tipo === 'imagen'): ?>
                    <div class="seccion-thumb seccion-thumb-extra">
                      <img src="../uploads/<?= htmlspecialchars(basename($valor)) ?>" alt="Imagen adicional sección <?= htmlspecialchars($sec['titulo']) ?>">
                    </div>
                  <?php elseif ($tipo === 'video'): ?>
                    <div class="video-embed-wrapper">
                      <?php if (str_contains($valor, 'youtube.com') || str_contains($valor, 'youtu.be')): ?>
                        <?php
                          $videoUrl = $valor;
                          if (str_contains($videoUrl, 'watch?v=')) {
                              $videoUrl = str_replace('watch?v=', 'embed/', $videoUrl);
                          }
                          if (str_contains($videoUrl, 'youtu.be/')) {
                              $videoUrl = str_replace('youtu.be/', 'youtube.com/embed/', $videoUrl);
                          }
                        ?>
                        <iframe src="<?= htmlspecialchars($videoUrl) ?>" title="Video sección" allowfullscreen loading="lazy"></iframe>
                      <?php else: ?>
                        <video controls preload="metadata">
                          <source src="<?= htmlspecialchars($valor) ?>">
                          Tu navegador no soporta video.
                        </video>
                      <?php endif; ?>
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
