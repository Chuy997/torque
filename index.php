<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_auth();

include __DIR__ . '/header.php';

// Última calibración por torque activo (por fecha, no por ID)
$query = "
  SELECT
    t.torqueID,
    t.foto,
    c.promedio,
    c.resultado,
    c.fechaCalibracion
  FROM torques t
  LEFT JOIN (
    SELECT torqueID, MAX(fechaCalibracion) AS last_fecha
    FROM calibrations
    GROUP BY torqueID
  ) latest ON latest.torqueID = t.torqueID
  LEFT JOIN calibrations c
    ON c.torqueID = latest.torqueID
   AND c.fechaCalibracion = latest.last_fecha
  WHERE t.status = 'activo'
  ORDER BY t.torqueID ASC
";

$pdo = pdo();
$stmt = $pdo->query($query);
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
      <?php foreach ($result as $row): ?>
        <tr>
          <td><?= htmlspecialchars($row['torqueID'], ENT_QUOTES, 'UTF-8') ?></td>
          <td>
            <?php if (!empty($row['foto'])): ?>
              <img src="<?= htmlspecialchars($row['foto'], ENT_QUOTES, 'UTF-8') ?>"
                   alt="Foto de <?= htmlspecialchars($row['torqueID'], ENT_QUOTES, 'UTF-8') ?>"
                   style="width: 100px; height: auto;">
            <?php else: ?>
              <em>Sin foto</em>
            <?php endif; ?>
          </td>
          <td><?= $row['promedio'] !== null ? htmlspecialchars(number_format($row['promedio'], 2), ENT_QUOTES, 'UTF-8') : '<em>Sin datos</em>' ?></td>
          <td><?= $row['resultado'] !== null ? htmlspecialchars($row['resultado'], ENT_QUOTES, 'UTF-8') : '<em>Sin calibración</em>' ?></td>
          <td><?= $row['fechaCalibracion'] !== null ? htmlspecialchars($row['fechaCalibracion'], ENT_QUOTES, 'UTF-8') : '<em>Nunca</em>' ?></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</main>
<?php include __DIR__ . '/footer.php'; ?>
