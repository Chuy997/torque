<?php
// /var/www/html/torque/api/dashboard_data.php
//require_once __DIR__ . '/../includes/bootstrap.php';
//require_login();
require_once __DIR__ . '/../config.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);

error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');

// === 1. KPIs ===
$now = date('Y-m-d');
$last30 = date('Y-m-d', strtotime('-30 days'));
$last3m = date('Y-m-d', strtotime('-3 months'));

// Aprobadas vs fallas últimos 30 días
$sql = "SELECT resultado, COUNT(*) AS cnt
        FROM calibrations
        WHERE fechaCalibracion >= ?
        GROUP BY resultado";
$stmt = $conn->prepare($sql);
$stmt->bind_param('s', $last30);
$stmt->execute();
$res = $stmt->get_result();
$aprobadas = 0; $fallas = 0;
while ($r = $res->fetch_assoc()) {
    if ($r['resultado'] === 'aprobado') $aprobadas = (int)$r['cnt'];
    else $fallas += (int)$r['cnt'];
}
$total = $aprobadas + $fallas;
$kpiAprobadas = $total > 0 ? round(($aprobadas/$total)*100,1) : 0;
$kpiFallas = $total > 0 ? round(($fallas/$total)*100,1) : 0;

// Torques fuera de uso
$sql = "SELECT COUNT(*) AS cnt FROM torques WHERE status='fuera de uso'";
$kpiFueraUso = $conn->query($sql)->fetch_assoc()['cnt'] ?? 0;

// Pendientes (usando la vista que ya creaste; ahora solo lunes)
$sql = "SELECT COUNT(*) AS cnt FROM vw_missing_calibrations_last3m";
$kpiPendientes = $conn->query($sql)->fetch_assoc()['cnt'] ?? 0;

// === 2. Serie semanal ===
// Agrupa por inicio de semana (lunes) y devuelve una etiqueta legible "YYYY-MM-DD → YYYY-MM-DD"
$sql = "SELECT
          DATE_SUB(DATE(fechaCalibracion), INTERVAL WEEKDAY(fechaCalibracion) DAY) AS week_start,
          SUM(resultado='aprobado') AS aprobadas,
          SUM(resultado='fuera de tolerancia') AS fallas,
          COUNT(*) AS total
        FROM calibrations
        WHERE fechaCalibracion >= ?
        GROUP BY week_start
        ORDER BY week_start";
$stmt = $conn->prepare($sql);
$stmt->bind_param('s', $last30);
$stmt->execute();
$res = $stmt->get_result();
$series = [];
while ($r = $res->fetch_assoc()) {
    $wkStart = new DateTime($r['week_start']);
    $wkEnd   = (clone $wkStart)->modify('+6 day');
    $label   = $wkStart->format('Y-m-d') . ' → ' . $wkEnd->format('Y-m-d');

    $pct = ((int)$r['total'] > 0)
        ? round(((int)$r['aprobadas'] / (int)$r['total']) * 100, 1)
        : 0.0;

    $series[] = [
        'semana'     => $label,
        'aprobadas'  => (int)$r['aprobadas'],
        'fallas'     => (int)$r['fallas'],
        'pct_ok'     => $pct, // % de aprobadas en esa semana
    ];
}

// === 3. Estados torques ===
// Devuelve SIEMPRE las 3 categorías, aunque no existan en la BD (valor 0).
$sql = "SELECT status, COUNT(*) AS cnt FROM torques GROUP BY status";
$res = $conn->query($sql);

// defaults
$estados = [
    'activo'               => 0,
    'fuera de uso'         => 0,
    'calibracion fallida'  => 0,
];

while ($r = $res->fetch_assoc()) {
    $status = $r['status'];
    if (isset($estados[$status])) {
        $estados[$status] = (int)$r['cnt'];
    }
}

// === 3b. Pareto de fallas (últimos 3 meses) ===
$sql = "SELECT torqueID, COUNT(*) AS fails
        FROM calibrations
        WHERE fechaCalibracion >= ? AND resultado='fuera de tolerancia'
        GROUP BY torqueID
        ORDER BY fails DESC, torqueID ASC
        LIMIT 10";
$stmt = $conn->prepare($sql);
$stmt->bind_param('s', $last3m);
$stmt->execute();
$res = $stmt->get_result();
$pareto = [];
while ($r = $res->fetch_assoc()) {
    $pareto[] = [
        'torqueID' => $r['torqueID'],
        'fails'    => (int)$r['fails'],
    ];
}

// === 4. Tabla detalle (últimos 20 torques activos) ===
// Tomamos la ÚLTIMA calibración por torque (incluye promedio y resultado)
$sql = "SELECT
          t.torqueID,
          lc.ultima,
          c.resultado,
          c.promedio,
          t.status
        FROM torques t
        LEFT JOIN (
          SELECT torqueID, MAX(fechaCalibracion) AS ultima
          FROM calibrations
          GROUP BY torqueID
        ) lc ON lc.torqueID = t.torqueID
        LEFT JOIN calibrations c
          ON c.torqueID = lc.torqueID
         AND c.fechaCalibracion = lc.ultima
        ORDER BY lc.ultima DESC
        LIMIT 20";

$res = $conn->query($sql);

$tabla = [];
while ($r = $res->fetch_assoc()) {
    $proxima = null;

    if (!empty($r['ultima'])) {
        // Próxima esperada: SIEMPRE el siguiente LUNES
        $dt  = new DateTime($r['ultima']);
        $dow = (int)$dt->format('N'); // 1=Lun ... 7=Dom
        $addDays = ($dow === 1) ? 7 : (8 - $dow);
        $dt->modify("+{$addDays} day")->setTime(0, 0, 0);
        $proxima = $dt->format('Y-m-d');
    }

    $tabla[] = [
        'id'        => $r['torqueID'],
        'ultima'    => $r['ultima'],
        'promedio'  => $r['promedio'],   // <-- nuevo campo
        'resultado' => $r['resultado'],
        'proxima'   => $proxima,
        'estado'    => $r['status']
    ];
}


// === Output ===
echo json_encode([
    'kpi' => [
        'aprobadas'  => $kpiAprobadas,
        'fallas'     => $kpiFallas,
        'pendientes' => $kpiPendientes,
        'fueraUso'   => $kpiFueraUso
    ],
    'series'  => $series,
    'estados' => $estados,
    'pareto'  => $pareto,
    'tabla'   => $tabla
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
