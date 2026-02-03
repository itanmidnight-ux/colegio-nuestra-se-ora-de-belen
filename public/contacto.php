<?php
require_once "../config.php";

$success = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nombre = trim($_POST["nombre"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $tipo = trim($_POST["tipo"] ?? "");
    $mensaje = trim($_POST["mensaje"] ?? "");

    if ($nombre === "" || $email === "" || $tipo === "" || $mensaje === "") {
        $error = "Por favor completa todos los campos.";
    } else {
        $create = "CREATE TABLE IF NOT EXISTS contactos (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nombre VARCHAR(120) NOT NULL,
            email VARCHAR(180) NOT NULL,
            tipo VARCHAR(60) NOT NULL,
            mensaje TEXT NOT NULL,
            creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        $conn->query($create);

        $stmt = $conn->prepare("INSERT INTO contactos (nombre, email, tipo, mensaje) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $nombre, $email, $tipo, $mensaje);
        if ($stmt->execute()) {
            $success = "Mensaje enviado correctamente. Gracias por contactarnos.";
        } else {
            $error = "No se pudo enviar el mensaje. Inténtalo de nuevo.";
        }
        $stmt->close();
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
  <title>Contacto - Institución Educativa Nuestra Señora de Belén</title>
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
        <span class="brand-sub">Contacto institucional</span>
      </div>
      <nav class="main-nav">
        <a href="index.php">Inicio</a>
        <a href="periodicos.php">Periódicos</a>
        <a href="#contacto-form">Formulario</a>
      </nav>
    </div>
  </header>

  <main>
    <section class="contact-hero" aria-labelledby="contact-title">
      <div class="hero-grid">
        <div class="hero-text">
          <p class="hero-kicker">Canales oficiales</p>
          <h1 id="contact-title">Ponte en contacto con nuestra institución</h1>
          <p class="hero-lead">Recibimos dudas, propuestas de colaboración e ideas para mejorar el proyecto institucional.</p>
        </div>
        <div class="hero-media">
          <div class="hero-card anim-card" style="--delay: 0.1s">
            <h2>Atención al público</h2>
            <p>Horario de atención: 6075920077</p>
            <p>Correo institucional: colnubelen@semcucuta.gov.co</p>
          </div>
        </div>
      </div>
    </section>

    <section class="contact-section" aria-labelledby="contact-info-title">
      <div class="section-header">
        <h2 id="contact-info-title">Formas de contacto</h2>
        <p>Selecciona el canal más conveniente para tu solicitud.</p>
      </div>
      <div class="contact-grid">
        <article class="contact-card anim-card" style="--delay: 0.05s">
          <img src="https://www.colnubelen.edu.co/images/botones/c1.png" alt="Canal institucional">
          <h3>Canal institucional</h3>
          <p>Institución Educativa Colegio Nuestra Señora de Belén</p>
          <p>Cúcuta - Norte Santander</p>
        </article>
        <article class="contact-card anim-card" style="--delay: 0.1s">
          <img src="https://www.colnubelen.edu.co/images/botones/e1.png" alt="Correo institucional">
          <h3>Correo electrónico</h3>
          <p>colnubelen@semcucuta.gov.co</p>
        </article>
        <article class="contact-card anim-card" style="--delay: 0.15s">
          <img src="https://www.colnubelen.edu.co/images/botones/p1.png" alt="Atención al público">
          <h3>Atención al público</h3>
          <p>Horario de atención: 6075920077</p>
        </article>
        <article class="contact-card anim-card" style="--delay: 0.2s">
          <img src="https://www.colnubelen.edu.co/images/botones/c1.png" alt="Rectoría">
          <h3>Rectora</h3>
          <p>CARLOS LUIS VILLAMIZAR RAMIREZ</p>
        </article>
      </div>

      <div class="contact-shell">
        <div class="contact-panel anim-card" style="--delay: 0.25s">
          <h3>Gestión de solicitudes</h3>
          <p>Escribe tu consulta o propuesta y nuestro equipo académico te responderá a través de los canales oficiales.</p>
          <ul>
            <li>Tiempo de respuesta: 24 a 72 horas</li>
            <li>Horario de atención: 6075920077</li>
            <li>Correo: colnubelen@semcucuta.gov.co</li>
          </ul>
        </div>
        <form class="contact-form" method="post" action="contacto.php" id="contacto-form">
          <?php if (!empty($success)): ?>
            <div class="form-alert success"><?php echo htmlspecialchars($success); ?></div>
          <?php endif; ?>
          <?php if (!empty($error)): ?>
            <div class="form-alert error"><?php echo htmlspecialchars($error); ?></div>
          <?php endif; ?>
          <div class="form-row">
            <div class="form-field">
              <label for="nombre">Nombre de usuario</label>
              <input type="text" id="nombre" name="nombre" placeholder="Tu nombre" required>
            </div>
            <div class="form-field">
              <label for="email">Correo electrónico</label>
              <input type="email" id="email" name="email" placeholder="tu@email.com" required>
            </div>
          </div>
          <div class="form-field" style="margin-top:16px;">
            <label for="tipo">Tipo de mensaje</label>
            <select id="tipo" name="tipo" required>
              <option value="duda">Duda o consulta</option>
              <option value="colaboracion">Propuesta de colaboración</option>
              <option value="idea">Ideas para mejorar la página</option>
            </select>
          </div>
          <div class="form-field" style="margin-top:16px;">
            <label for="mensaje">Mensaje</label>
            <textarea id="mensaje" name="mensaje" placeholder="Escribe tu mensaje" required></textarea>
          </div>
          <div class="form-actions">
            <button type="submit" class="btn-primary">Enviar mensaje</button>
          </div>
        </form>
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
