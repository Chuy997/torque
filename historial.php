<?php
// /var/www/html/torque/historial.php
require_once __DIR__ . '/includes/bootstrap.php';
require_auth(); // cualquier usuario logueado puede ver el historial

include __DIR__ . '/header.php';

$pdo = pdo();
$stmt = $pdo->query("
    SELECT historyID, torqueID, action, date
    FROM history
    ORDER BY date DESC
");
$records = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<main class="container mt-5">
    <h1 class="mb-4">Historial de Torques</h1>

    <table class="table table-dark standard-table">
        <thead>
            <tr>
                <th>ID Historial</th>
                <th>Torque ID</th>
                <th>Acción</th>
                <th>Fecha</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($records as $row): ?>
                <tr>
                    <td><?= htmlspecialchars($row['historyID'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($row['torqueID'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($row['action'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($row['date'], ENT_QUOTES, 'UTF-8') ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</main>

<?php include __DIR__ . '/footer.php'; ?>
