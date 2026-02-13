<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'No autorizado']);
    exit;
}
require_once "../config.php";

$action = $_GET['action'] ?? $_POST['action'] ?? 'list';
$allowedLocations = ['on_entry','on_header_nav','on_virtual_read_end','on_download','on_sections_menu'];
$userId = intval($_SESSION['user_id'] ?? 0) ?: null;

function json_out($arr) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($arr);
    exit;
}

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

function logSurveyHistory(mysqli $conn, ?int $encuestaId, string $accion, array $payload, ?int $usuarioId): void
{
    $payloadJson = json_encode($payload, JSON_UNESCAPED_UNICODE);
    $stmt = $conn->prepare("INSERT INTO encuesta_historial (encuesta_id, accion, payload_json, usuario_id) VALUES (?, ?, ?, ?)");
    $stmt->bind_param('issi', $encuestaId, $accion, $payloadJson, $usuarioId);
    $stmt->execute();
    $stmt->close();
}

function refreshSurveyMetrics(mysqli $conn, int $surveyId): array
{
    $stmt = $conn->prepare("SELECT o.id opcion_id, o.texto, COUNT(r.id) total
        FROM encuesta_opciones o
        LEFT JOIN encuesta_respuestas r ON r.opcion_id = o.id
        WHERE o.encuesta_id = ?
        GROUP BY o.id, o.texto
        ORDER BY o.orden_visual ASC, o.id ASC");
    $stmt->bind_param('i', $surveyId);
    $stmt->execute();
    $res = $stmt->get_result();

    $stats = [];
    $totalGeneral = 0;
    while ($row = $res->fetch_assoc()) {
        $row['total'] = (int)$row['total'];
        $totalGeneral += $row['total'];
        $stats[] = $row;
    }
    $stmt->close();

    $stmtUpsert = $conn->prepare("INSERT INTO encuesta_metricas (encuesta_id, opcion_id, total_respuestas, porcentaje)
        VALUES (?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE total_respuestas = VALUES(total_respuestas), porcentaje = VALUES(porcentaje)");

    foreach ($stats as &$row) {
        $percentage = $totalGeneral > 0 ? round(($row['total'] * 100) / $totalGeneral, 2) : 0.00;
        $row['porcentaje'] = $percentage;
        $stmtUpsert->bind_param('iiid', $surveyId, $row['opcion_id'], $row['total'], $percentage);
        $stmtUpsert->execute();
    }
    $stmtUpsert->close();

    return $stats;
}

ensureSurveyExtraTables($conn);

if ($action === 'list') {
    $res = $conn->query("SELECT id, titulo, pregunta, ubicacion, activa, creada_en FROM encuestas ORDER BY creada_en DESC");
    $rows = [];
    while ($r = $res->fetch_assoc()) $rows[] = $r;
    json_out(['status' => 'ok', 'items' => $rows]);
}

if ($action === 'create') {
    $titulo = trim($_POST['titulo'] ?? '');
    $pregunta = trim($_POST['pregunta'] ?? '');
    $ubicacion = $_POST['ubicacion'] ?? 'on_entry';
    $opciones = array_values(array_filter(array_map('trim', explode("\n", $_POST['opciones'] ?? ''))));

    if (!$titulo || !$pregunta || count($opciones) < 2) json_out(['status' => 'error', 'message' => 'Completa título, pregunta y mínimo 2 opciones']);
    if (!in_array($ubicacion, $allowedLocations, true)) json_out(['status' => 'error', 'message' => 'Ubicación inválida']);

    $stmt = $conn->prepare("UPDATE encuestas SET activa = 0, finalizada_en = COALESCE(finalizada_en, NOW()) WHERE ubicacion = ? AND activa = 1");
    $stmt->bind_param('s', $ubicacion);
    $stmt->execute();
    $stmt->close();

    $stmt = $conn->prepare("INSERT INTO encuestas (titulo, pregunta, ubicacion, activa) VALUES (?, ?, ?, 1)");
    $stmt->bind_param('sss', $titulo, $pregunta, $ubicacion);
    $stmt->execute();
    $id = (int)$stmt->insert_id;
    $stmt->close();

    $i = 1;
    $stmtOpt = $conn->prepare("INSERT INTO encuesta_opciones (encuesta_id, texto, orden_visual) VALUES (?, ?, ?)");
    foreach ($opciones as $opt) {
        $stmtOpt->bind_param('isi', $id, $opt, $i);
        $stmtOpt->execute();
        $i++;
    }
    $stmtOpt->close();

    refreshSurveyMetrics($conn, $id);
    logSurveyHistory($conn, $id, 'create', ['titulo' => $titulo, 'ubicacion' => $ubicacion, 'opciones' => $opciones], $userId);

    json_out(['status' => 'ok']);
}

if ($action === 'detail') {
    $id = intval($_GET['id'] ?? 0);
    $stmt = $conn->prepare("SELECT id, titulo, pregunta, ubicacion, activa, creada_en FROM encuestas WHERE id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $enc = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if (!$enc) json_out(['status' => 'error', 'message' => 'No encontrada']);

    $opts = [];
    $stmt = $conn->prepare("SELECT id, texto FROM encuesta_opciones WHERE encuesta_id = ? ORDER BY orden_visual ASC, id ASC");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($r = $res->fetch_assoc()) $opts[] = $r;
    $stmt->close();

    $stats = refreshSurveyMetrics($conn, $id);
    json_out(['status' => 'ok', 'encuesta' => $enc, 'opciones' => $opts, 'stats' => $stats]);
}

if ($action === 'save') {
    $id = intval($_POST['id'] ?? 0);
    $titulo = trim($_POST['titulo'] ?? '');
    $pregunta = trim($_POST['pregunta'] ?? '');
    $ubicacion = $_POST['ubicacion'] ?? 'on_entry';
    $opciones = array_values(array_filter(array_map('trim', explode("\n", $_POST['opciones'] ?? ''))));

    if ($id <= 0 || !$titulo || !$pregunta || count($opciones) < 2) json_out(['status' => 'error', 'message' => 'Datos incompletos']);
    if (!in_array($ubicacion, $allowedLocations, true)) json_out(['status' => 'error', 'message' => 'Ubicación inválida']);

    $stmt = $conn->prepare("UPDATE encuestas SET activa = 0, finalizada_en = COALESCE(finalizada_en, NOW()) WHERE ubicacion = ? AND id <> ? AND activa = 1");
    $stmt->bind_param('si', $ubicacion, $id);
    $stmt->execute();
    $stmt->close();

    $stmt = $conn->prepare("UPDATE encuestas SET titulo=?, pregunta=?, ubicacion=?, activa=1, finalizada_en=NULL WHERE id=?");
    $stmt->bind_param('sssi', $titulo, $pregunta, $ubicacion, $id);
    $stmt->execute();
    $stmt->close();

    $stmtDel = $conn->prepare("DELETE FROM encuesta_opciones WHERE encuesta_id = ?");
    $stmtDel->bind_param('i', $id);
    $stmtDel->execute();
    $stmtDel->close();

    $stmtOpt = $conn->prepare("INSERT INTO encuesta_opciones (encuesta_id, texto, orden_visual) VALUES (?, ?, ?)");
    $i = 1;
    foreach ($opciones as $opt) {
        $stmtOpt->bind_param('isi', $id, $opt, $i);
        $stmtOpt->execute();
        $i++;
    }
    $stmtOpt->close();

    refreshSurveyMetrics($conn, $id);
    logSurveyHistory($conn, $id, 'update', ['titulo' => $titulo, 'ubicacion' => $ubicacion, 'opciones' => $opciones], $userId);

    json_out(['status' => 'ok']);
}

if ($action === 'finish') {
    $id = intval($_POST['id'] ?? 0);
    $stmt = $conn->prepare("UPDATE encuestas SET activa=0, finalizada_en=NOW() WHERE id=?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $stmt->close();

    logSurveyHistory($conn, $id, 'finish', ['message' => 'Encuesta finalizada'], $userId);
    json_out(['status' => 'ok']);
}

if ($action === 'delete') {
    $id = intval($_POST['id'] ?? 0);
    logSurveyHistory($conn, $id, 'delete', ['message' => 'Encuesta eliminada'], $userId);

    $stmt = $conn->prepare("DELETE FROM encuestas WHERE id=?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $stmt->close();
    json_out(['status' => 'ok']);
}

if ($action === 'download_pdf') {
    $id = intval($_GET['id'] ?? 0);
    $stmt = $conn->prepare("SELECT titulo, pregunta, ubicacion FROM encuestas WHERE id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $enc = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if (!$enc) die('Encuesta no encontrada');

    $stats = refreshSurveyMetrics($conn, $id);

    $text = "Reporte de encuesta\\n" . $enc['titulo'] . "\\n" . $enc['pregunta'] . "\\n\\n";
    foreach ($stats as $s) {
        $text .= $s['texto'] . ': ' . $s['total'] . " respuestas (" . $s['porcentaje'] . "%)\\n";
    }
    $stream = "BT /F1 12 Tf 50 760 Td (" . str_replace(['\\','(',')'], ['\\\\','\\(','\\)'], $text) . ") Tj ET";
    $objs = [];
    $objs[] = "1 0 obj << /Type /Catalog /Pages 2 0 R >> endobj";
    $objs[] = "2 0 obj << /Type /Pages /Kids [3 0 R] /Count 1 >> endobj";
    $objs[] = "3 0 obj << /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] /Contents 4 0 R /Resources << /Font << /F1 5 0 R >> >> >> endobj";
    $objs[] = "4 0 obj << /Length " . strlen($stream) . " >> stream\n" . $stream . "\nendstream endobj";
    $objs[] = "5 0 obj << /Type /Font /Subtype /Type1 /BaseFont /Helvetica >> endobj";

    $pdf = "%PDF-1.4\n";
    $offsets = [0];
    foreach ($objs as $o) {
        $offsets[] = strlen($pdf);
        $pdf .= $o . "\n";
    }
    $xref = strlen($pdf);
    $pdf .= "xref\n0 6\n0000000000 65535 f \n";
    for ($i = 1; $i <= 5; $i++) {
        $pdf .= sprintf("%010d 00000 n \n", $offsets[$i]);
    }
    $pdf .= "trailer << /Size 6 /Root 1 0 R >>\nstartxref\n{$xref}\n%%EOF";

    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="encuesta_' . $id . '.pdf"');
    echo $pdf;
    exit;
}

json_out(['status' => 'error', 'message' => 'Acción inválida']);
