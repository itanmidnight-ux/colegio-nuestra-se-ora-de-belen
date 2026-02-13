<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'No autorizado']);
    exit;
}
require_once "../config.php";

$action = $_GET['action'] ?? $_POST['action'] ?? 'list';

function json_out($arr) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($arr);
    exit;
}

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

    $stmt = $conn->prepare("INSERT INTO encuestas (titulo, pregunta, ubicacion, activa) VALUES (?, ?, ?, 1)");
    $stmt->bind_param('sss', $titulo, $pregunta, $ubicacion);
    $stmt->execute();
    $id = $stmt->insert_id;
    $stmt->close();

    $i = 1;
    $stmtOpt = $conn->prepare("INSERT INTO encuesta_opciones (encuesta_id, texto, orden_visual) VALUES (?, ?, ?)");
    foreach ($opciones as $opt) {
        $stmtOpt->bind_param('isi', $id, $opt, $i);
        $stmtOpt->execute();
        $i++;
    }
    $stmtOpt->close();

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

    $opts=[];
    $stmt = $conn->prepare("SELECT id, texto FROM encuesta_opciones WHERE encuesta_id = ? ORDER BY orden_visual ASC, id ASC");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $res=$stmt->get_result();
    while($r=$res->fetch_assoc()) $opts[]=$r;
    $stmt->close();

    $stats=[];
    $stmt = $conn->prepare("SELECT o.id opcion_id, o.texto, COUNT(r.id) total
      FROM encuesta_opciones o
      LEFT JOIN encuesta_respuestas r ON r.opcion_id = o.id
      WHERE o.encuesta_id = ?
      GROUP BY o.id, o.texto
      ORDER BY o.orden_visual ASC, o.id ASC");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $res=$stmt->get_result();
    while($r=$res->fetch_assoc()) $stats[]=$r;
    $stmt->close();

    json_out(['status'=>'ok','encuesta'=>$enc,'opciones'=>$opts,'stats'=>$stats]);
}

if ($action === 'save') {
    $id = intval($_POST['id'] ?? 0);
    $titulo = trim($_POST['titulo'] ?? '');
    $pregunta = trim($_POST['pregunta'] ?? '');
    $ubicacion = $_POST['ubicacion'] ?? 'on_entry';
    $opciones = array_values(array_filter(array_map('trim', explode("\n", $_POST['opciones'] ?? ''))));

    $stmt = $conn->prepare("UPDATE encuestas SET titulo=?, pregunta=?, ubicacion=? WHERE id=?");
    $stmt->bind_param('sssi', $titulo, $pregunta, $ubicacion, $id);
    $stmt->execute();
    $stmt->close();

    $conn->query("DELETE FROM encuesta_opciones WHERE encuesta_id = {$id}");
    $stmtOpt = $conn->prepare("INSERT INTO encuesta_opciones (encuesta_id, texto, orden_visual) VALUES (?, ?, ?)");
    $i=1;
    foreach($opciones as $opt){
        $stmtOpt->bind_param('isi', $id, $opt, $i);
        $stmtOpt->execute();
        $i++;
    }
    $stmtOpt->close();

    json_out(['status'=>'ok']);
}

if ($action === 'finish') {
    $id = intval($_POST['id'] ?? 0);
    $stmt = $conn->prepare("UPDATE encuestas SET activa=0, finalizada_en=NOW() WHERE id=?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $stmt->close();
    json_out(['status'=>'ok']);
}

if ($action === 'delete') {
    $id = intval($_POST['id'] ?? 0);
    $stmt = $conn->prepare("DELETE FROM encuestas WHERE id=?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $stmt->close();
    json_out(['status'=>'ok']);
}

if ($action === 'download_pdf') {
    $id = intval($_GET['id'] ?? 0);
    $stmt = $conn->prepare("SELECT titulo, pregunta, ubicacion FROM encuestas WHERE id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $enc = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if (!$enc) die('Encuesta no encontrada');

    $stats=[];
    $stmt = $conn->prepare("SELECT o.texto, COUNT(r.id) total
      FROM encuesta_opciones o
      LEFT JOIN encuesta_respuestas r ON r.opcion_id = o.id
      WHERE o.encuesta_id = ?
      GROUP BY o.id, o.texto
      ORDER BY o.orden_visual ASC, o.id ASC");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $res=$stmt->get_result();
    while($r=$res->fetch_assoc()) $stats[]=$r;
    $stmt->close();

    $content = "%PDF-1.3\n";
    $text = "Reporte de encuesta\\n" . $enc['titulo'] . "\\n" . $enc['pregunta'] . "\\n\\n";
    foreach($stats as $s){ $text .= $s['texto'] . ': ' . $s['total'] . " respuestas\\n"; }
    $stream = "BT /F1 12 Tf 50 760 Td (" . str_replace(['\\','(',')'], ['\\\\','\\(','\\)'], $text) . ") Tj ET";
    $objs = [];
    $objs[] = "1 0 obj << /Type /Catalog /Pages 2 0 R >> endobj";
    $objs[] = "2 0 obj << /Type /Pages /Kids [3 0 R] /Count 1 >> endobj";
    $objs[] = "3 0 obj << /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] /Contents 4 0 R /Resources << /Font << /F1 5 0 R >> >> >> endobj";
    $objs[] = "4 0 obj << /Length " . strlen($stream) . " >> stream\n".$stream."\nendstream endobj";
    $objs[] = "5 0 obj << /Type /Font /Subtype /Type1 /BaseFont /Helvetica >> endobj";

    $pdf = "%PDF-1.4\n";
    $offsets = [0];
    foreach($objs as $o){ $offsets[] = strlen($pdf); $pdf .= $o."\n"; }
    $xref = strlen($pdf);
    $pdf .= "xref\n0 6\n0000000000 65535 f \n";
    for($i=1;$i<=5;$i++) $pdf .= sprintf("%010d 00000 n \n", $offsets[$i]);
    $pdf .= "trailer << /Size 6 /Root 1 0 R >>\nstartxref\n{$xref}\n%%EOF";

    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="encuesta_'.$id.'.pdf"');
    echo $pdf;
    exit;
}

json_out(['status' => 'error', 'message' => 'Acción inválida']);
