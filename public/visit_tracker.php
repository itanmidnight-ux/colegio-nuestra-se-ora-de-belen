<?php

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
}

function registerSectionVisit(mysqli $conn, string $section): void
{
    ensureVisitsTables($conn);
    $section = trim($section);
    if ($section === '') {
        $section = 'general';
    }

    $today = date('Y-m-d');

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
}
