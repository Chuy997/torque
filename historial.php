<?php
// /var/www/html/torque/historial.php
require_once __DIR__ . '/includes/bootstrap.php'; // sesión segura + helpers + $conn
require_login(); // cualquier usuario logueado puede ver el historial

include __DIR__ . '/header.php';

// Obtener historial completo ordenado
$result = $conn->query(
    "SELECT historyID, torqueID, action, date
     FROM history
     ORDER BY date DESC"
);
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
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['historyID'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($row['torqueID'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($row['action'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($row['date'], ENT_QUOTES, 'UTF-8') ?></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</main>

<?php include __DIR__ . '/footer.php'; ?>
