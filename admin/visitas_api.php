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
            $fecha = $dia['fecha'];
            $stmt = $conn->prepare("SELECT seccion, visitas FROM visitas_secciones_diarias WHERE fecha = ? ORDER BY visitas DESC, seccion ASC");
            $stmt->bind_param('s', $fecha);
            $stmt->execute();
            $resSec = $stmt->get_result();

            $secciones = [];
            while ($s = $resSec->fetch_assoc()) {
                $secciones[] = [
                    'seccion' => $s['seccion'],
                    'visitas' => (int)$s['visitas'],
                ];
            }
            $stmt->close();

            $items[] = [
                'fecha' => $fecha,
                'total_visitas' => (int)$dia['total_visitas'],
                'secciones' => $secciones,
            ];
        }
    }

    echo json_encode(['status' => 'ok', 'items' => $items]);
    exit;
}


if ($action === 'raw_devices_carousel') {
    $items = [];
    $resDias = $conn->query("SELECT fecha, COUNT(*) AS dispositivos_brutos FROM visitas_dispositivos_diarias GROUP BY fecha ORDER BY fecha ASC");
    if ($resDias) {
        while ($dia = $resDias->fetch_assoc()) {
            $items[] = [
                'fecha' => $dia['fecha'],
                'dispositivos_brutos' => (int)$dia['dispositivos_brutos'],
            ];
        }
    }

    echo json_encode(['status' => 'ok', 'items' => $items]);
    exit;
}

echo json_encode(['status' => 'error', 'message' => 'Acción no soportada']);
