<?php

const VISITS_TIMEZONE = 'America/Bogota';

function getVisitsToday(): string
{
    $tz = new DateTimeZone(VISITS_TIMEZONE);
    return (new DateTimeImmutable('now', $tz))->format('Y-m-d');
}

function ensureVisitsTables(mysqli $conn): void
{
    $conn->query("CREATE TABLE IF NOT EXISTS visitas_secciones_diarias (
        id INT AUTO_INCREMENT PRIMARY KEY,
        seccion VARCHAR(120) NOT NULL,
        fecha DATE NOT NULL,
        visitas INT NOT NULL DEFAULT 0,
        actualizado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY uq_seccion_fecha (seccion, fecha),
        INDEX idx_visitas_fecha (fecha),
        INDEX idx_visitas_seccion (seccion)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $conn->query("CREATE TABLE IF NOT EXISTS visitas_totales_diarias (
        id INT AUTO_INCREMENT PRIMARY KEY,
        fecha DATE NOT NULL,
        total_visitas INT NOT NULL DEFAULT 0,
        actualizado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY uq_fecha_total (fecha),
        INDEX idx_totales_fecha (fecha)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $conn->query("CREATE TABLE IF NOT EXISTS visitas_dispositivos_diarias (
        id INT AUTO_INCREMENT PRIMARY KEY,
        fecha DATE NOT NULL,
        identificador_dispositivo CHAR(64) NOT NULL,
        creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY uq_dispositivo_fecha (fecha, identificador_dispositivo),
        INDEX idx_dispositivo_fecha (fecha)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
}

function registerSectionVisit(mysqli $conn, string $section): void
{
    ensureVisitsTables($conn);
    $section = trim($section);
    if ($section === '') {
        $section = 'general';
    }

    $today = getVisitsToday();

    $stmtSection = $conn->prepare("INSERT INTO visitas_secciones_diarias (seccion, fecha, visitas)
        VALUES (?, ?, 1)
        ON DUPLICATE KEY UPDATE visitas = visitas + 1");
    if ($stmtSection) {
        $stmtSection->bind_param('ss', $section, $today);
        $stmtSection->execute();
        $stmtSection->close();
    }

    $stmtTotal = $conn->prepare("INSERT INTO visitas_totales_diarias (fecha, total_visitas)
        VALUES (?, 1)
        ON DUPLICATE KEY UPDATE total_visitas = total_visitas + 1");
    if ($stmtTotal) {
        $stmtTotal->bind_param('s', $today);
        $stmtTotal->execute();
        $stmtTotal->close();
    }

    $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown-agent';
    $deviceId = hash('sha256', $ip . '|' . $userAgent);

    $stmtDevice = $conn->prepare("INSERT IGNORE INTO visitas_dispositivos_diarias (fecha, identificador_dispositivo)
        VALUES (?, ?)");
    if ($stmtDevice) {
        $stmtDevice->bind_param('ss', $today, $deviceId);
        $stmtDevice->execute();
        $stmtDevice->close();
    }
}
