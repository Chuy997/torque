<?php
// /var/www/html/torque/menu.php
?>
<nav class="navbar navbar-expand-lg navbar-dark bg-black border-bottom border-secondary">
  <div class="container-fluid">
    <a class="navbar-brand fw-bold text-light" href="index.php">Torque Manager</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
            aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <!-- Nuevo acceso directo al Dashboard -->
        <li class="nav-item"><a class="nav-link" href="dashboard.php">Dashboard</a></li>

        <li class="nav-item"><a class="nav-link" href="agregar_torque.php">Agregar</a></li>
        <li class="nav-item"><a class="nav-link" href="actualizar_torque.php">Actualizar</a></li>
        <li class="nav-item"><a class="nav-link" href="calibracion.php">Calibrar</a></li>
        <li class="nav-item"><a class="nav-link" href="fuera_de_uso.php">Reactivar</a></li>
        <li class="nav-item"><a class="nav-link" href="historial.php">Historial</a></li>
      </ul>

      <ul class="navbar-nav">
        <li class="nav-item"><a class="nav-link text-danger" href="logout.php">Salir</a></li>
      </ul>
    </div>
  </div>
</nav>
