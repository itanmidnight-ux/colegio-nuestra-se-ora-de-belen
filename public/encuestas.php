<?php
require_once "../config.php";
header('Content-Type: application/json; charset=utf-8');

$action = $_GET['action'] ?? $_POST['action'] ?? '';

if ($action === 'active') {
    $context = $_GET['context'] ?? 'on_entry';
    $allowed = ['on_entry','on_header_nav','on_virtual_read_end','on_download','on_sections_menu'];
    if (!in_array($context, $allowed, true)) {
        echo json_encode(['status' => 'error', 'message' => 'Contexto no válido']);
        exit;
    }

    $stmt = $conn->prepare("SELECT id, titulo, pregunta FROM encuestas WHERE activa = 1 AND ubicacion = ? ORDER BY creada_en DESC LIMIT 1");
    $stmt->bind_param('s', $context);
    $stmt->execute();
    $survey = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$survey) {
        echo json_encode(['status' => 'empty']);
        exit;
    }

    $opts = [];
    $stmt = $conn->prepare("SELECT id, texto FROM encuesta_opciones WHERE encuesta_id = ? ORDER BY orden_visual ASC, id ASC");
    $stmt->bind_param('i', $survey['id']);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $opts[] = $row;
    }
    $stmt->close();

    echo json_encode(['status' => 'ok', 'survey' => $survey, 'options' => $opts]);
    exit;
}

if ($action === 'answer' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $surveyId = intval($_POST['encuesta_id'] ?? 0);
    $optionId = intval($_POST['opcion_id'] ?? 0);
    $context = trim($_POST['contexto'] ?? '');
    $token = $_COOKIE['encuesta_token'] ?? '';

    if ($surveyId <= 0 || $optionId <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'Datos incompletos']);
        exit;
    }

    if (!$token) {
        $token = bin2hex(random_bytes(16));
        setcookie('encuesta_token', $token, time() + (86400 * 365), '/');
    }

    $stmt = $conn->prepare("INSERT INTO encuesta_respuestas (encuesta_id, opcion_id, session_token, contexto) VALUES (?, ?, ?, ?)");
    $stmt->bind_param('iiss', $surveyId, $optionId, $token, $context);
    $ok = $stmt->execute();
    $stmt->close();

    echo json_encode($ok ? ['status' => 'ok'] : ['status' => 'error', 'message' => 'No se pudo guardar']);
    exit;
}

echo json_encode(['status' => 'error', 'message' => 'Acción no soportada']);
