<?php
// /var/www/html/torque/header.php
require_once __DIR__ . '/includes/bootstrap.php'; // garantiza sesión segura
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Torque Management</title>

    <!-- Bootstrap 5 (solo CSS, dark mode friendly) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH"
          crossorigin="anonymous">

    <!-- Estilos propios -->
    <link rel="stylesheet" href="css/styles.css">
</head>
<body class="bg-dark text-light d-flex flex-column min-vh-100">
    <?php include __DIR__ . '/menu.php'; ?>
    <div class="flex-grow-1"><!-- abre wrapper para contenido -->
