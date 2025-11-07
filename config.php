<?php

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

    
} catch (mysqli_sql_exception $e) {
    error_log('[DB] Error de conexión: ' . $e->getMessage());
    http_response_code(500);
    exit('Error de conexión a base de datos.');
}
