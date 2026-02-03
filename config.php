<?php
$servername = "localhost";
$username   = "root";     // usuario por defecto en XAMPP
$password   = "";         // sin contraseña
$dbname     = "periodico_db"; // nombre de la base que creaste

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("❌ Error de conexión: " . $conn->connect_error);
}
$conn->set_charset("utf8mb4");
?>
