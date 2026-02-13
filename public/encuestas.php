<?php
require_once "../config.php";
header('Content-Type: application/json; charset=utf-8');

$action = $_GET['action'] ?? $_POST['action'] ?? '';

function ensureSurveyExtraTables(mysqli $conn): void
{
    $conn->query("CREATE TABLE IF NOT EXISTS encuesta_historial (
        id INT AUTO_INCREMENT PRIMARY KEY,
        encuesta_id INT NULL,
        accion ENUM('create','update','finish','delete','answer') NOT NULL,
        payload_json LONGTEXT,
        usuario_id INT NULL,
        creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        CONSTRAINT fk_historial_encuesta FOREIGN KEY (encuesta_id) REFERENCES encuestas(id) ON DELETE SET NULL,
        CONSTRAINT fk_historial_usuario FOREIGN KEY (usuario_id) REFERENCES users(id) ON DELETE SET NULL,
        INDEX idx_historial_encuesta (encuesta_id),
        INDEX idx_historial_accion (accion),
        INDEX idx_historial_fecha (creado_en)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $conn->query("CREATE TABLE IF NOT EXISTS encuesta_metricas (
        id INT AUTO_INCREMENT PRIMARY KEY,
        encuesta_id INT NOT NULL,
        opcion_id INT NOT NULL,
        total_respuestas INT NOT NULL DEFAULT 0,
        porcentaje DECIMAL(5,2) NOT NULL DEFAULT 0,
        actualizado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        CONSTRAINT fk_metricas_encuesta FOREIGN KEY (encuesta_id) REFERENCES encuestas(id) ON DELETE CASCADE,
        CONSTRAINT fk_metricas_opcion FOREIGN KEY (opcion_id) REFERENCES encuesta_opciones(id) ON DELETE CASCADE,
        UNIQUE KEY uq_metricas_encuesta_opcion (encuesta_id, opcion_id),
        INDEX idx_metricas_encuesta (encuesta_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
}

function refreshSurveyMetrics(mysqli $conn, int $surveyId): void
{
    $stmt = $conn->prepare("SELECT o.id opcion_id, COUNT(r.id) total
        FROM encuesta_opciones o
        LEFT JOIN encuesta_respuestas r ON r.opcion_id = o.id
        WHERE o.encuesta_id = ?
        GROUP BY o.id
        ORDER BY o.orden_visual ASC, o.id ASC");
    $stmt->bind_param('i', $surveyId);
    $stmt->execute();
    $res = $stmt->get_result();

    $rows = [];
    $totalGeneral = 0;
    while ($row = $res->fetch_assoc()) {
        $row['total'] = (int)$row['total'];
        $totalGeneral += $row['total'];
        $rows[] = $row;
    }
    $stmt->close();

    $stmtUpsert = $conn->prepare("INSERT INTO encuesta_metricas (encuesta_id, opcion_id, total_respuestas, porcentaje)
        VALUES (?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE total_respuestas = VALUES(total_respuestas), porcentaje = VALUES(porcentaje)");

    foreach ($rows as $row) {
        $percentage = $totalGeneral > 0 ? round(($row['total'] * 100) / $totalGeneral, 2) : 0.00;
        $stmtUpsert->bind_param('iiid', $surveyId, $row['opcion_id'], $row['total'], $percentage);
        $stmtUpsert->execute();
    }
    $stmtUpsert->close();
}

function logSurveyHistory(mysqli $conn, int $surveyId, int $optionId, string $context): void
{
    $accion = 'answer';
    $payloadJson = json_encode(['opcion_id' => $optionId, 'contexto' => $context], JSON_UNESCAPED_UNICODE);
    $userId = null;

    $stmt = $conn->prepare("INSERT INTO encuesta_historial (encuesta_id, accion, payload_json, usuario_id) VALUES (?, ?, ?, ?)");
    $stmt->bind_param('issi', $surveyId, $accion, $payloadJson, $userId);
    $stmt->execute();
    $stmt->close();
}

ensureSurveyExtraTables($conn);

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

    $stmt = $conn->prepare("SELECT id FROM encuestas WHERE id = ? AND activa = 1");
    $stmt->bind_param('i', $surveyId);
    $stmt->execute();
    $surveyValid = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if (!$surveyValid) {
        echo json_encode(['status' => 'error', 'message' => 'Encuesta no disponible']);
        exit;
    }

    $stmt = $conn->prepare("SELECT id FROM encuesta_opciones WHERE id = ? AND encuesta_id = ?");
    $stmt->bind_param('ii', $optionId, $surveyId);
    $stmt->execute();
    $optionValid = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if (!$optionValid) {
        echo json_encode(['status' => 'error', 'message' => 'Opción inválida']);
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

    if ($ok) {
        refreshSurveyMetrics($conn, $surveyId);
        logSurveyHistory($conn, $surveyId, $optionId, $context);
    }

    echo json_encode($ok ? ['status' => 'ok'] : ['status' => 'error', 'message' => 'No se pudo guardar']);
    exit;
}

echo json_encode(['status' => 'error', 'message' => 'Acción no soportada']);
