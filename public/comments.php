<?php
require_once "../config.php";
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_periodico = intval($_POST['id_periodico']);
    $usuario = htmlspecialchars($_POST['usuario']);
    $comentario = htmlspecialchars($_POST['comentario']);

    $stmt = $conn->prepare("INSERT INTO comentarios (periodico_id, usuario_nombre, comentario, creado_en) VALUES (?, ?, ?, NOW())");
    $stmt->bind_param("iss", $id_periodico, $usuario, $comentario);

    if ($stmt->execute()) {
        $response = ['status' => 'ok', 'message' => 'Comentario enviado', 'usuario' => $usuario, 'comentario' => $comentario, 'fecha' => date("Y-m-d H:i:s")];
        echo json_encode($response);
    } else {
        $response = ['status' => 'error', 'message' => 'Error al guardar el comentario'];
        echo json_encode($response);
    }
    $stmt->close();
} else {
    $response = ['status' => 'error', 'message' => 'Método de solicitud no válido'];
    echo json_encode($response);
}
?>
