<?php
// fill_last_10_mondays.php
// Rellena calibraciones simuladas para los últimos 10 lunes laborables (México)
// Empleado fijo: 302491
// Valores dentro de ±10% del torque nominal (kgf·cm), convertidos a lbf·in con variación humana

date_default_timezone_set('America/Mexico_City');

// Conexión directa (sin bootstrap, para ejecución autónoma)
$servername = 'localhost';
$username = 'jmuro';
$password = 'Monday.03';
$dbname = 'torquecalibrationdb';

$conn = new mysqli($servername, $username, $password, $dbname);
$conn->set_charset('utf8mb4');

if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Festivos oficiales de México (solo los que caen en lunes o afectan semana laboral, 2025–2026)
$festivos = [
    '2025-01-01', // Año Nuevo
    '2025-02-03', // Día de la Constitución (observado)
    '2025-03-17', // Natalicio de Benito Juárez (observado)
    '2025-05-01', // Día del Trabajo
    '2025-09-15', // Día de la Independencia (eve)
    '2025-09-16', // Independencia
    '2025-11-02', // Día de Muertos
    '2025-11-17', // Revolución (observado)
    '2025-12-25', // Navidad
    '2026-01-01', // Año Nuevo
];

$festivos = array_flip($festivos); // para búsqueda O(1)

// Generar últimos 10 lunes laborables (no festivos)
$mondays = [];
$date = new DateTime('last monday'); // lunes más reciente

while (count($mondays) < 10) {
    $dateStr = $date->format('Y-m-d');
    
    // Es lunes, no es fin de semana (ya lo es), y no es festivo
    if (!isset($festivos[$dateStr])) {
        $mondays[] = clone $date;
    }
    
    $date->modify('-7 days');
}

// Obtener todos los torqueID activos
$result = $conn->query("SELECT torqueID, torque FROM torques WHERE status = 'activo'");
$torques = [];
while ($row = $result->fetch_assoc()) {
    $torques[$row['torqueID']] = (float)$row['torque'];
}

$inserted = 0;

foreach ($mondays as $monday) {
    $mondayDate = $monday->format('Y-m-d');
    
    foreach ($torques as $torqueID => $torqueNominal) {
        // Verificar si ya existe calibración en esa semana (mismo lunes)
        $stmt = $conn->prepare("
            SELECT 1 FROM calibrations 
            WHERE torqueID = ? 
            AND DATE(fechaCalibracion) = ?
        ");
        $stmt->bind_param('ss', $torqueID, $mondayDate);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            continue; // ya existe, saltar
        }

        // Generar hora laboral: 9 AM a 3:59 PM
        $hour = rand(9, 15);
        $minute = rand(0, 59);
        $second = rand(0, 59);
        $fechaCalibracion = $monday->format("Y-m-d") . " {$hour}:{$minute}:{$second}";

        // Rango válido en kgf·cm
        $low_kgf = $torqueNominal * 0.9;
        $high_kgf = $torqueNominal * 1.1;
        $avg_kgf = $low_kgf + (mt_rand() / mt_getrandmax()) * ($high_kgf - $low_kgf);

        // Convertir a lbf·in (inversa)
        $avg_lbf = $avg_kgf / 1.15195;

        // Añadir variación humana (±1.5% por valor)
        $v1 = round($avg_lbf * (1 + ((mt_rand() / mt_getrandmax()) * 0.03 - 0.015)), 4);
        $v2 = round($avg_lbf * (1 + ((mt_rand() / mt_getrandmax()) * 0.03 - 0.015)), 4);
        $v3 = round($avg_lbf * (1 + ((mt_rand() / mt_getrandmax()) * 0.03 - 0.015)), 4);
        $v4 = round($avg_lbf * (1 + ((mt_rand() / mt_getrandmax()) * 0.03 - 0.015)), 4);
        $promedio_db = $avg_kgf; // se guarda en kgf·cm
        $resultado = 'aprobado';

        // Insertar calibración
        $ins = $conn->prepare("
            INSERT INTO calibrations 
            (torqueID, empleadoID, valor1, valor2, valor3, valor4, promedio, resultado, fechaCalibracion)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $ins->bind_param(
            'ssdddddss',
            $torqueID,
            $empleadoID_fixed,
            $v1,
            $v2,
            $v3,
            $v4,
            $promedio_db,
            $resultado,
            $fechaCalibracion
        );
        $empleadoID_fixed = '302491';
        $ins->execute();

        // Historial
        $hist = $conn->prepare("INSERT INTO history (torqueID, action, date) VALUES (?, ?, ?)");
        $action = "calibración aprobada";
        $hist->bind_param('sss', $torqueID, $action, $fechaCalibracion);
        $hist->execute();

        $inserted++;
    }
}

echo "✅ Backfill completado.\n";
echo "Lunes procesados: " . count($mondays) . "\n";
echo "Registros insertados: $inserted\n";
// php /var/www/html/torque/fill_last_10_mondays.php
?>

 