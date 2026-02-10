<?php
require_once "../config.php";
$conn->query("CREATE TABLE IF NOT EXISTS secciones_periodico (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(180) NOT NULL,
    descripcion TEXT,
    contenido LONGTEXT,
    imagen VARCHAR(255),
    orden_visual INT DEFAULT 0,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    actualizado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$secciones = [];
$res = $conn->query("SELECT titulo, descripcion, contenido, imagen FROM secciones_periodico ORDER BY orden_visual ASC, creado_en DESC");
if ($res) while ($r = $res->fetch_assoc()) $secciones[] = $r;
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
    <div class="periodicos-list">
      <?php if (count($secciones) > 0): ?>
        <?php foreach ($secciones as $sec): ?>
          <article class="periodico-card anim-card">
            <?php if (!empty($sec['imagen'])): ?>
              <div class="periodico-thumb"><img src="../uploads/<?= htmlspecialchars($sec['imagen']) ?>" alt="Imagen sección <?= htmlspecialchars($sec['titulo']) ?>" style="width:100%;height:100%;object-fit:cover;"></div>
            <?php endif; ?>
            <h3><?= htmlspecialchars($sec['titulo']) ?></h3>
            <p><?= htmlspecialchars($sec['descripcion']) ?></p>
            <p><?= nl2br(htmlspecialchars($sec['contenido'])) ?></p>
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
