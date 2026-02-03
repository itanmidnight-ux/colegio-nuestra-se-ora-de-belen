<?php
session_start();
require_once "../config.php";

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    die(json_encode(['status' => 'error', 'message' => '❌ No autorizado.']));
}

header('Content-Type: application/json');

$action = $_POST['action'] ?? 'add';
$usuario_id = $_SESSION['user_id'];

if ($action === "upload_only") {
    if (!isset($_FILES['archivo']) || $_FILES['archivo']['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(['status'=>'error','message'=>'❌ Archivo PDF no proporcionado o inválido.']);
        exit;
    }
    $archivoTmp = $_FILES['archivo']['tmp_name'];
    $archivoExt = strtolower(pathinfo($_FILES['archivo']['name'], PATHINFO_EXTENSION));
    if($archivoExt!=="pdf"){
        echo json_encode(['status'=>'error','message'=>'❌ Solo se permiten archivos PDF.']);
        exit;
    }
    $nuevoNombre = uniqid("periodico_").".pdf";
    $rutaFinal = "../uploads/".$nuevoNombre;
    if(move_uploaded_file($archivoTmp,$rutaFinal)){
        echo json_encode(['status'=>'ok','filename'=>$nuevoNombre]);
    } else {
        echo json_encode(['status'=>'error','message'=>'❌ Error al mover el archivo.']);
    }
    exit;
}

// Lógica para guardar los datos del periódico junto con el archivo
if($action === "add"){
    $titulo = htmlspecialchars($_POST['titulo']);
    $director = htmlspecialchars($_POST['director']);
    $participantes = htmlspecialchars($_POST['participantes']);
    $descripcion = htmlspecialchars($_POST['descripcion']);
    $fecha = $_POST['fecha'];
    $archivo_pdf = $_POST['archivo_pdf'];

    $stmt = $conn->prepare("INSERT INTO periodicos (titulo, director, participantes, descripcion, publicado_en, archivo_pdf, usuario_id) VALUES (?,?,?,?,?,?,?)");
    $stmt->bind_param("ssssssi", $titulo, $director, $participantes, $descripcion, $fecha, $archivo_pdf, $usuario_id);
    if($stmt->execute()){
        echo json_encode(['status'=>'ok','message'=>'✅ Periódico agregado correctamente.']);
    } else {
        echo json_encode(['status'=>'error','message'=>'❌ Error al guardar en la base de datos.']);
    }
    $stmt->close();
    exit;
}
?>
