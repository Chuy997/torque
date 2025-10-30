<?php
// /var/www/html/torque/index.php
require_once __DIR__ . '/includes/bootstrap.php'; // sesión segura + helpers + $conn
require_login(); // exige usuario autenticado

include __DIR__ . '/header.php';

// Consulta para obtener las calibraciones más recientes SOLO de torques activos
$query = "SELECT t.torqueID, t.foto, c.promedio, c.resultado, c.fechaCalibracion
FROM torques t
JOIN calibrations c ON t.torqueID = c.torqueID
WHERE t.status = 'activo'
  AND c.calibrationID = (SELECT MAX(calibrationID) FROM calibrations WHERE torqueID = t.torqueID)
ORDER BY c.fechaCalibracion DESC";

$result = $conn->query($query);
?>
<main class="container mt-5">
  <h1>Registros de Calibración Recientes</h1>
  <table class="table table-dark standard-table">
    <thead>
      <tr>
        <th>ID Torque</th>
        <th>Foto</th>
        <th>Promedio</th>
        <th>Resultado</th>
        <th>Fecha</th>
      </tr>
    </thead>
    <tbody>
      <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
          <td><?= htmlspecialchars($row['torqueID'], ENT_QUOTES, 'UTF-8') ?></td>
          <td>
            <img src="<?= htmlspecialchars($row['foto'], ENT_QUOTES, 'UTF-8') ?>"
                 alt="Foto de <?= htmlspecialchars($row['torqueID'], ENT_QUOTES, 'UTF-8') ?>"
                 style="width: 100px; height: auto;">
          </td>
          <td><?= htmlspecialchars($row['promedio'], ENT_QUOTES, 'UTF-8') ?></td>
          <td><?= htmlspecialchars($row['resultado'], ENT_QUOTES, 'UTF-8') ?></td>
          <td><?= htmlspecialchars($row['fechaCalibracion'], ENT_QUOTES, 'UTF-8') ?></td>
        </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
</main>
<?php include __DIR__ . '/footer.php'; ?>
