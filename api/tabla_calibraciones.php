<?php
// /var/www/html/torque/api/tabla_calibraciones.php
// API que devuelve la última calibración por torque (incluye promedio, resultado y próxima)
// Usa MAX(calibrationID) por torque para evitar problemas con igualdad exacta de timestamps.
require_once __DIR__ . '/../config.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json; charset=utf-8');

$sql = "
  SELECT
    t.torqueID,
    c.fechaCalibracion AS ultima,
    c.resultado,
    c.promedio,
    t.status
  FROM torques t
  LEFT JOIN (
    SELECT torqueID, MAX(calibrationID) AS lastID
    FROM calibrations
    GROUP BY torqueID
  ) lc ON lc.torqueID = t.torqueID
  LEFT JOIN calibrations c
         ON c.calibrationID = lc.lastID
  ORDER BY c.fechaCalibracion DESC
  LIMIT 200
";

$res = $conn->query($sql);
$tabla = [];

while ($r = $res->fetch_assoc()) {
    // Próxima esperada: SIEMPRE el siguiente LUNES
    $proxima = null;
    if (!empty($r['ultima'])) {
        $dt  = new DateTime($r['ultima']);
        $dow = (int)$dt->format('N'); // 1=Lun ... 7=Dom
        $add = ($dow === 1) ? 7 : (8 - $dow);
        $dt->modify("+{$add} day")->setTime(0, 0, 0);
        $proxima = $dt->format('Y-m-d');
    }

    $tabla[] = [
        'id'        => $r['torqueID'],
        'ultima'    => $r['ultima'],
        'promedio'  => isset($r['promedio']) ? (float)$r['promedio'] : null,
        'resultado' => $r['resultado'] ?? null,
        'proxima'   => $proxima,
        'estado'    => $r['status']
    ];
}

echo json_encode(
    ['tabla' => $tabla],
    JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
);
