<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.html");
    exit;
}
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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'upload_image') {
        if (!isset($_FILES['imagen']) || $_FILES['imagen']['error'] !== UPLOAD_ERR_OK) {
            echo json_encode(["status" => "error", "message" => "No se recibió una imagen válida."]);
            exit;
        }

        $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
        $tmp = $_FILES['imagen']['tmp_name'];
        $mime = mime_content_type($tmp);
        if (!isset($allowed[$mime])) {
            echo json_encode(["status" => "error", "message" => "Formato no permitido. Usa JPG, PNG o WEBP."]);
            exit;
        }

        $filename = 'seccion_' . uniqid() . '.' . $allowed[$mime];
        $dest = "../uploads/" . $filename;
        if (!move_uploaded_file($tmp, $dest)) {
            echo json_encode(["status" => "error", "message" => "No se pudo guardar la imagen."]);
            exit;
        }

        echo json_encode(["status" => "ok", "filename" => $filename]);
        exit;
    }

    if ($action === 'add') {
        $titulo = trim($_POST['titulo'] ?? '');
        $descripcion = trim($_POST['descripcion'] ?? '');
        $contenido = trim($_POST['contenido'] ?? '');
        $imagen = trim($_POST['imagen'] ?? '');
        $contenido_extra = trim($_POST['contenido_extra'] ?? '[]');
        $orden = intval($_POST['orden_visual'] ?? 0);

        if ($titulo === '') {
            echo json_encode(["status" => "error", "message" => "El título es obligatorio."]);
            exit;
        }

        $stmt = $conn->prepare("INSERT INTO secciones_periodico (titulo, descripcion, contenido, imagen, contenido_extra, orden_visual) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssi", $titulo, $descripcion, $contenido, $imagen, $contenido_extra, $orden);
        $ok = $stmt->execute();
        $stmt->close();

        echo json_encode(["status" => $ok ? "ok" : "error", "message" => $ok ? "Sección creada con éxito." : "No se pudo crear la sección."]);
        exit;
    }

    if ($action === 'edit') {
        $id = intval($_POST['id'] ?? 0);
        $titulo = trim($_POST['titulo'] ?? '');
        $descripcion = trim($_POST['descripcion'] ?? '');
        $contenido = trim($_POST['contenido'] ?? '');
        $imagen = trim($_POST['imagen'] ?? '');
        $contenido_extra = trim($_POST['contenido_extra'] ?? '[]');
        $orden = intval($_POST['orden_visual'] ?? 0);

        if ($id <= 0 || $titulo === '') {
            echo json_encode(["status" => "error", "message" => "Datos inválidos para editar."]);
            exit;
        }

        $stmt = $conn->prepare("UPDATE secciones_periodico SET titulo=?, descripcion=?, contenido=?, imagen=?, contenido_extra=?, orden_visual=? WHERE id=?");
        $stmt->bind_param("sssssii", $titulo, $descripcion, $contenido, $imagen, $contenido_extra, $orden, $id);
        $ok = $stmt->execute();
        $stmt->close();

        echo json_encode(["status" => $ok ? "ok" : "error", "message" => $ok ? "Sección actualizada." : "No se pudo actualizar la sección."]);
        exit;
    }

    if ($action === 'delete') {
        $id = intval($_POST['id'] ?? 0);
        if ($id <= 0) {
            echo json_encode(["status" => "error", "message" => "Sección inválida."]);
            exit;
        }

        $stmt = $conn->prepare("SELECT imagen, contenido_extra FROM secciones_periodico WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->bind_result($imagenPrincipal, $contenidoExtra);
        $stmt->fetch();
        $stmt->close();

        if (!empty($imagenPrincipal) && file_exists("../uploads/" . $imagenPrincipal)) {
            unlink("../uploads/" . $imagenPrincipal);
        }

        $bloques = json_decode($contenidoExtra ?? '[]', true);
        if (is_array($bloques)) {
            foreach ($bloques as $bloque) {
                if (($bloque['tipo'] ?? '') === 'imagen' && !empty($bloque['valor'])) {
                    $imgFile = basename($bloque['valor']);
                    $imgPath = "../uploads/" . $imgFile;
                    if (file_exists($imgPath)) {
                        unlink($imgPath);
                    }
                }
            }
        }

        $stmt = $conn->prepare("DELETE FROM secciones_periodico WHERE id = ?");
        $stmt->bind_param("i", $id);
        $ok = $stmt->execute();
        $stmt->close();

        echo json_encode(["status" => $ok ? "ok" : "error", "message" => $ok ? "Sección eliminada." : "No se pudo eliminar la sección."]);
        exit;
    }
}
