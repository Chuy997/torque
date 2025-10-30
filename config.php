<?php
// /var/www/html/torque/config.php
// Objetivo: conexión robusta a MariaDB con nombre de BD correcto (todo minúsculas),
// errores como excepciones y charset/colación seguros.

// IMPORTANTE: En tu servidor la base real se llama 'torquecalibrationdb' (minúsculas).
// Tu código usaba "TorqueCalibrationDB" (camel case), lo que en Linux suele fallar.
// Esta versión lo corrige y activa excepciones para detectar problemas pronto.

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$servername = 'localhost';
$username   = 'jmuro';
$password   = 'Monday.03';   // Manténlo seguro
$dbname     = 'torquecalibrationdb'; // <-- nombre corregido en minúsculas

try {
    $conn = new mysqli($servername, $username, $password, $dbname);
    // Fuerza charset seguro para evitar problemas con acentos/símbolos
    if (!$conn->set_charset('utf8mb4')) {
        throw new mysqli_sql_exception('No se pudo establecer utf8mb4: ' . $conn->error);
    }

    // Opcional: modo SQL más estricto (comentado; lo activaremos después si todo ok)
    // $conn->query("SET SESSION sql_mode = 'STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION'");

} catch (mysqli_sql_exception $e) {
    error_log('[DB] Error de conexión: ' . $e->getMessage());
    http_response_code(500);
    exit('Error de conexión a base de datos.');
}
