<?php
// /var/www/html/torque/dashboard.php
require_once __DIR__ . '/includes/bootstrap.php';
require_login();

include __DIR__ . '/header.php';
?>
<main class="container-fluid py-4">
  <h1 class="text-center mb-4"> Dashboard torques</h1>

  <!-- KPIs -->
  <div class="row text-center mb-4">
    <div class="col-md-3 mb-3">
      <div class="card bg-dark border border-secondary shadow-sm">
        <div class="card-body">
          <h5 class="card-title text-success">Aprobadas (30 días)</h5>
          <p id="kpi-aprobadas" class="display-6 text-white fw-bold">-- %</p>
        </div>
      </div>
    </div>
    <div class="col-md-3 mb-3">
      <div class="card bg-dark border border-secondary shadow-sm">
        <div class="card-body">
          <h5 class="card-title text-danger">Fuera de tolerancia</h5>
          <p id="kpi-falla" class="display-6 text-white fw-bold">-- %</p>
        </div>
      </div>
    </div>
    <div class="col-md-3 mb-3">
      <div class="card bg-dark border border-secondary shadow-sm">
        <div class="card-body">
          <h5 class="card-title text-warning">Pendientes</h5>
          <p id="kpi-pendientes" class="display-6 text-white fw-bold">--</p>
        </div>
      </div>
    </div>
    <div class="col-md-3 mb-3">
      <div class="card bg-dark border border-secondary shadow-sm">
        <div class="card-body">
          <h5 class="card-title text-info">Fuera de uso</h5>
          <p id="kpi-fuerauso" class="display-6 text-white fw-bold">--</p>
        </div>
      </div>
    </div>
  </div>

  <!-- Gráficas fila 1 -->
  <div class="row">
    <div class="col-lg-6 mb-4">
      <div class="card bg-dark border border-secondary shadow-sm">
        <div class="card-header">Aprobadas vs Fallas (semanal)</div>
        <div class="card-body">
          <canvas id="chartBar"></canvas>
        </div>
      </div>
    </div>
    <div class="col-lg-6 mb-4">
      <div class="card bg-dark border border-secondary shadow-sm">
        <div class="card-header">Tendencia histórica (% aprobadas por semana)</div>
        <div class="card-body">
          <canvas id="chartLine"></canvas>
        </div>
      </div>
    </div>
  </div>

  <!-- Gráficas fila 2 -->
  <div class="row">
    <div class="col-lg-6 mb-4">
      <div class="card bg-dark border border-secondary shadow-sm">
        <div class="card-header">Estados de Torques</div>
        <div class="card-body">
          <canvas id="chartDonut"></canvas>
        </div>
      </div>
    </div>
    <div class="col-lg-6 mb-4">
      <div class="card bg-dark border border-secondary shadow-sm">
        <div class="card-header">Pareto de fallas (últimos 3 meses)</div>
        <div class="card-body">
          <canvas id="chartPareto"></canvas>
        </div>
      </div>
    </div>
  </div>

   <!-- Tabla dinámica -->
  <div class="card bg-dark border border-secondary shadow-sm">
    <div class="card-header">Detalle de Calibraciones</div>
    <div class="card-body table-responsive">
      <table class="table table-dark table-hover align-middle">
        <thead>
          <tr>
            <th>Torque ID</th>
            <th>Última Calibración</th>
            <th>Promedio (Kgf/cm)</th> <!-- nueva columna -->
            <th>Resultado</th>
            <th>Próxima Esperada (Lunes)</th>
            <th>Estado</th>
          </tr>
        </thead>
        <tbody id="tabla-detalle">
          <!-- JS llenará filas -->
        </tbody>
      </table>
    </div>
  </div>


<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="js/dashboard.js"></script>

<?php include __DIR__ . '/footer.php'; ?>
