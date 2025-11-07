<?php
// /var/www/html/torque/api/tabla_calibraciones.php
// API que devuelve la última calibración por torque (incluye promedio, resultado y próxima)
require_once __DIR__ . '/../config.php';

header('Content-Type: application/json; charset=utf-8');

// Obtener última calibración por torque usando la fecha más reciente (no ID)
$sql = "
  SELECT
    t.torqueID,
    c.fechaCalibracion AS ultima,
    c.resultado,
    c.promedio,
    t.status
  FROM torques t
  LEFT JOIN (
    SELECT torqueID, MAX(fechaCalibracion) AS max_fecha
    FROM calibrations
    GROUP BY torqueID
  ) latest ON latest.torqueID = t.torqueID
  LEFT JOIN calibrations c
    ON c.torqueID = latest.torqueID
   AND c.fechaCalibracion = latest.max_fecha
  WHERE t.status = 'activo'
  ORDER BY t.torqueID ASC
";

$res = $conn->query($sql);
$tabla = [];

while ($r = $res->fetch_assoc()) {
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
    JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
);