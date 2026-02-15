<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['status' => 'error', 'message' => 'No autorizado']);
    exit;
}

require_once "../config.php";
require_once "../public/visit_tracker.php";
ensureVisitsTables($conn);
header('Content-Type: application/json; charset=utf-8');

$action = $_GET['action'] ?? '';

if ($action === 'summary') {
    $totalVisitas = 0;
    $totalDias = 0;
    $ultimaFecha = null;
    $totalDispositivos = 0;

    $resTotal = $conn->query("SELECT COALESCE(SUM(total_visitas),0) total, COUNT(*) dias, MAX(fecha) ultima FROM visitas_totales_diarias");
    if ($resTotal) {
        $row = $resTotal->fetch_assoc();
        $totalVisitas = (int)($row['total'] ?? 0);
        $totalDias = (int)($row['dias'] ?? 0);
        $ultimaFecha = $row['ultima'] ?? null;
    }

    $secciones = [];
    $resDispositivos = $conn->query("SELECT COUNT(*) total FROM visitas_dispositivos_diarias");
    if ($resDispositivos) {
        $rowDispositivos = $resDispositivos->fetch_assoc();
        $totalDispositivos = (int)($rowDispositivos['total'] ?? 0);
    }

    $resSecciones = $conn->query("SELECT seccion, COALESCE(SUM(visitas),0) total FROM visitas_secciones_diarias GROUP BY seccion ORDER BY total DESC");
    if ($resSecciones) {
        while ($r = $resSecciones->fetch_assoc()) {
            $secciones[] = ['seccion' => $r['seccion'], 'total' => (int)$r['total']];
        }
    }

    echo json_encode([
        'status' => 'ok',
        'summary' => [
            'total_visitas' => $totalVisitas,
            'total_dias' => $totalDias,
            'ultima_fecha' => $ultimaFecha,
            'total_dispositivos_registrados' => $totalDispositivos,
            'secciones' => $secciones,
        ]
    ]);
    exit;
}

if ($action === 'daily_carousel') {
    $items = [];
    $resDias = $conn->query("SELECT fecha, total_visitas FROM visitas_totales_diarias ORDER BY fecha ASC");
    if ($resDias) {
        while ($dia = $resDias->fetch_assoc()) {
            $items[] = [
                'fecha' => $dia['fecha'],
                'total_visitas' => (int)$dia['total_visitas'],
            ];
        }
    }

    echo json_encode(['status' => 'ok', 'items' => $items]);
    exit;
}

if ($action === 'raw_weekly') {
    $today = getVisitsToday();
    $date = new DateTimeImmutable($today, new DateTimeZone(VISITS_TIMEZONE));
    $dayOfWeek = (int)$date->format('N');
    $monday = $date->modify('-' . ($dayOfWeek - 1) . ' days');

    $weekDates = [];
    for ($i = 0; $i < 7; $i += 1) {
        $weekDates[] = $monday->modify('+' . $i . ' days')->format('Y-m-d');
    }

    $stmt = $conn->prepare("SELECT fecha, total_visitas FROM visitas_totales_diarias WHERE fecha BETWEEN ? AND ? ORDER BY fecha ASC");
    $start = $weekDates[0];
    $end = $weekDates[6];
    $mapByDate = [];

    if ($stmt) {
        $stmt->bind_param('ss', $start, $end);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $mapByDate[$row['fecha']] = (int)$row['total_visitas'];
        }
        $stmt->close();
    }

    $items = [];
    foreach ($weekDates as $weekDate) {
        $items[] = [
            'fecha' => $weekDate,
            'visitas_brutas' => $mapByDate[$weekDate] ?? 0,
        ];
    }

    echo json_encode(['status' => 'ok', 'items' => $items]);
    exit;
}

echo json_encode(['status' => 'error', 'message' => 'Acción no soportada']);
