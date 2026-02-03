<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Acceso Administrativo - ECO BELÉN</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="login-style.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&family=Merriweather:wght@400;700&display=swap" rel="stylesheet">
</head>
<body>
  <a class="corner-logo" href="index.php" aria-label="Ir al inicio de acceso">
    <img src="escudo.png" alt="Escudo Institucional">
  </a>
  <div class="login-wrapper">
    <div class="login-card">
      <div class="login-header">
        <div class="logo">ECO BELÉN</div>
        <h2>Acceso Administrativo</h2>
      </div>

      <!-- Mostrar mensaje de error (si existe) -->
      <?php if (isset($_GET['error'])): ?>
        <div class="login-alert">
          <?php
            $err = $_GET['error'];
            if ($err === 'campos') echo "Por favor completa todos los campos.";
            elseif ($err === 'usuario') echo "Usuario no encontrado.";
            elseif ($err === 'clave') echo "Contraseña incorrecta.";
            else echo "Error de autenticación.";
          ?>
        </div>
      <?php endif; ?>

      <form action="auth.php" method="POST" class="login-form">
        <div class="input-group">
          <input type="text" id="usuario" name="usuario" required>
          <label for="usuario">Usuario o Email</label>
        </div>
        <div class="input-group">
          <input type="password" id="password" name="password" required>
          <label for="password">Contraseña</label>
        </div>
        <div class="options">
          <label><input type="checkbox" name="remember"> Mantener sesión</label>
        </div>
        <button type="submit" class="btn-login">Ingresar</button>
      </form>
    </div>
  </div>
</body>
</html>
