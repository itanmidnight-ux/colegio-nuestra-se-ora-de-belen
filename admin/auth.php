<?php
session_start();
require_once __DIR__ . "/../config.php"; // ruta relativa: admin/auth.php -> ../config.php

// Forzar charset
if (isset($conn) && method_exists($conn, 'set_charset')) {
    $conn->set_charset("utf8mb4");
}

// Obtener valores (acepta 'usuario' o 'email')
$usuario_input = '';
if (!empty($_POST['usuario'])) $usuario_input = trim($_POST['usuario']);
elseif (!empty($_POST['email'])) $usuario_input = trim($_POST['email']);

$password = isset($_POST['password']) ? $_POST['password'] : '';

// Validación básica de campos
if ($usuario_input === '' || $password === '') {
    // campos vacíos
    header("Location: index.php?error=campos");
    exit();
}

// Preparar búsqueda por nombre o email
$sql = "SELECT id, nombre, email, password, rol FROM users WHERE nombre = ? OR email = ? LIMIT 1";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    // Error de SQL (raro). Para debugging, puedes habilitar log aquí.
    header("Location: index.php?error=sql");
    exit();
}
$stmt->bind_param("ss", $usuario_input, $usuario_input);
$stmt->execute();
$result = $stmt->get_result();

if (!$result || $result->num_rows === 0) {
    // usuario no encontrado
    header("Location: index.php?error=usuario");
    exit();
}

$user = $result->fetch_assoc();
$stored = $user['password'];

// Detectar si stored parece ser bcrypt (común: comienza con $2y$ / $2a$ / $2b$)
$is_bcrypt = preg_match('/^\$2[aby]\$/', $stored);

// Verificación: si es bcrypt usamos password_verify, si no comparamos en texto plano
$ok = false;
if ($is_bcrypt) {
    if (password_verify($password, $stored)) {
        $ok = true;
    }
} else {
    // texto plano
    if ($password === $stored) {
        $ok = true;
    }
}

if ($ok) {
    // Login exitoso: guardar sesión y redirigir
    $_SESSION['user_id'] = (int)$user['id'];
    $_SESSION['username'] = $user['nombre'];
    $_SESSION['user_role'] = $user['rol'] ?? 'editor';
    header("Location: dashboard.php");
    exit();
} else {
    header("Location: index.php?error=clave");
    exit();
}
