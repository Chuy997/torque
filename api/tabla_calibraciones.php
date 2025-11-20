<?php
require_once __DIR__ . '/../includes/bootstrap.php';


header('Content-Type: application/json');

// Festivos mexicanos 2025 (formato Y-m-d)
$festivos = [
    '2025-01-01', // Año Nuevo
    '2025-02-03', // Día de la Constitución
    '2025-03-17', // Natalicio de Benito Juárez
    '2025-05-01', // Día del Trabajo
    '2025-09-16', // Independencia
    '2025-11-17', // Revolución Mexicana (movido desde 20/11)
    '2025-12-25'  // Navidad
];

try {
    $pdo = pdo();

    // Obtener última calibración por torque
    $stmt = $pdo->query("
        SELECT
            t.torqueID AS id,
            c.fechaCalibracion AS ultima,
            c.promedio,
            c.resultado,
            t.status AS estado
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
    ");

    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $result = [];

    foreach ($rows as $row) {
        // Formatear campos existentes
        $ultima = $row['ultima'] ? date('Y-m-d', strtotime($row['ultima'])) : null;
        
        // Calcular próxima calibración: siguiente lunes tras 7 días
        $proxima = null;
        if ($row['ultima']) {
            $base = strtotime('+7 days', strtotime($row['ultima']));
            $dayOfWeek = date('N', $base); // 1=lunes, 7=domingo

            if ($dayOfWeek == 1) {
                $proxima = date('Y-m-d', $base);
            } else {
                // Saltar al lunes de la próxima semana
                $daysToMonday = 8 - $dayOfWeek;
                $proxima = date('Y-m-d', strtotime("+$daysToMonday days", $base));
            }

            // Si el lunes es festivo, mover a martes
            while (in_array($proxima, $festivos)) {
                $proxima = date('Y-m-d', strtotime('+1 day', strtotime($proxima)));
            }
        }

        $result[] = [
            'id' => $row['id'],
            'ultima' => $ultima,
            'promedio' => $row['promedio'] !== null ? (float)$row['promedio'] : null,
            'resultado' => $row['resultado'] ?: null,
            'estado' => $row['estado'] ?: 'desconocido',
            'proxima' => $proxima
        ];
    }

    echo json_encode(['tabla' => $result], JSON_NUMERIC_CHECK);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error al cargar calibraciones']);
}
