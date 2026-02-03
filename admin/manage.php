<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}
require_once "../config.php";

// Insertar periódico
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $titulo = $_POST['titulo'];
    $director = $_POST['director'];
    $participantes = $_POST['participantes'] ?? '';
    $fecha = $_POST['fecha'];
    $descripcion = $_POST['descripcion'] ?? '';
    $archivo_pdf = $_POST['archivo_pdf'];

    $sql = "INSERT INTO periodicos (titulo, director, participantes, publicado_en, descripcion, archivo_pdf) 
            VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssss", $titulo, $director, $participantes, $fecha, $descripcion, $archivo_pdf);
    $stmt->execute();
    $stmt->close();

    echo json_encode(["status" => "success", "message" => "Periódico agregado con éxito"]);
    exit;
}

// Editar periódico
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit') {
    $id = $_POST['id'];
    $titulo = $_POST['titulo'];
    $director = $_POST['director'];
    $fecha = $_POST['fecha'];
    $descripcion = $_POST['descripcion'];

    $sql = "UPDATE periodicos SET titulo=?, director=?, publicado_en=?, descripcion=? WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssi", $titulo, $director, $fecha, $descripcion, $id);
    $stmt->execute();
    $stmt->close();

    echo json_encode(["status" => "success", "message" => "Periódico editado con éxito"]);
    exit;
}

// Eliminar periódico
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $id = $_POST['id'];

    // Obtener archivo para borrar
    $sql = "SELECT archivo_pdf FROM periodicos WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->bind_result($archivo_pdf);
    $stmt->fetch();
    $stmt->close();

    if ($archivo_pdf && file_exists("../uploads/" . $archivo_pdf)) {
        unlink("../uploads/" . $archivo_pdf);
    }

    // Borrar de la base de datos
    $sql = "DELETE FROM periodicos WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();

    echo json_encode(["status" => "success", "message" => "Periódico eliminado con éxito"]);
    exit;
}

// Obtener lista de periódicos
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'list') {
    $sql = "SELECT id, titulo, director, publicado_en, descripcion, archivo_pdf FROM periodicos ORDER BY publicado_en DESC";
    $result = $conn->query($sql);
    $periodicos = [];

    while ($row = $result->fetch_assoc()) {
        $periodicos[] = $row;
    }

    echo json_encode($periodicos);
    exit;
}
?>
