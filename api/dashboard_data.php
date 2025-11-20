<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_auth();

header('Content-Type: application/json');
header('Cache-Control: no-cache');

try {
    $pdo = pdo();

    // --- KPIs ---
    // Total de calibraciones en últimos 30 días
    $stmt = $pdo->prepare("
        SELECT 
            SUM(CASE WHEN resultado = 'aprobado' THEN 1 ELSE 0 END) AS aprobadas,
            SUM(CASE WHEN resultado = 'fuera de tolerancia' THEN 1 ELSE 0 END) AS fallas,
            COUNT(*) AS total_30d
        FROM calibrations 
        WHERE fechaCalibracion >= DATE_SUB(NOW(), INTERVAL 30 DAY)
    ");
    $stmt->execute();
    $kpi30 = $stmt->fetch(PDO::FETCH_ASSOC);
    $total_30d = (int)($kpi30['total_30d'] ?? 0);
    $aprobadas_pct = $total_30d > 0 ? round(100 * ($kpi30['aprobadas'] / $total_30d), 1) : 0;
    $fallas_pct = $total_30d > 0 ? round(100 * ($kpi30['fallas'] / $total_30d), 1) : 0;

    // Torques pendientes (activo pero sin calibración en últimos 30 días)
    $stmt = $pdo->query("
        SELECT COUNT(*) 
        FROM torques t
        LEFT JOIN calibrations c ON c.torqueID = t.torqueID AND c.fechaCalibracion >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        WHERE t.status = 'activo' AND c.torqueID IS NULL
    ");
    $pendientes = (int)$stmt->fetchColumn();

    // Torques fuera de uso
    $stmt = $pdo->query("SELECT COUNT(*) FROM torques WHERE status IN ('fuera de uso', 'calibracion fallida')");
    $fuera_uso = (int)$stmt->fetchColumn();

    // --- Datos para gráficas (simplificados) ---
    // Últimas 8 semanas
    $stmt = $pdo->query("
        SELECT 
            YEARWEEK(fechaCalibracion, 1) AS semana,
            SUM(CASE WHEN resultado = 'aprobado' THEN 1 ELSE 0 END) AS aprobadas,
            SUM(CASE WHEN resultado = 'fuera de tolerancia' THEN 1 ELSE 0 END) AS fallas
        FROM calibrations 
        WHERE fechaCalibracion >= DATE_SUB(NOW(), INTERVAL 8 WEEK)
        GROUP BY semana
        ORDER BY semana
    ");
    $chartBar = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Estados actuales
    $stmt = $pdo->query("
        SELECT status, COUNT(*) AS count
        FROM torques
        GROUP BY status
    ");
    $chartDonut = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // --- Tabla de calibraciones recientes ---
    $stmt = $pdo->query("
        SELECT
            t.torqueID,
            c.promedio,
            c.resultado,
            c.fechaCalibracion,
            t.status
        FROM torques t
        LEFT JOIN (
            SELECT torqueID, MAX(fechaCalibracion) AS last_fecha
            FROM calibrations
            GROUP BY torqueID
        ) latest ON latest.torqueID = t.torqueID
        LEFT JOIN calibrations c
            ON c.torqueID = latest.torqueID
            AND c.fechaCalibracion = latest.last_fecha
        ORDER BY c.fechaCalibracion DESC
        LIMIT 50
    ");
    $tabla = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'kpi' => [
            'aprobadas_pct' => $aprobadas_pct,
            'fallas_pct' => $fallas_pct,
            'pendientes' => $pendientes,
            'fuera_uso' => $fuera_uso
        ],
        'chartBar' => $chartBar,
        'chartDonut' => $chartDonut,
        'tabla' => $tabla
    ], JSON_NUMERIC_CHECK);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error interno: ' . $e->getMessage()]);
}
