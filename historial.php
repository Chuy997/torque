<?php
include 'config.php';
include 'header.php';
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// Obtener el historial completo
$query = "SELECT * FROM History ORDER BY date DESC";
$result = $conn->query($query);
?>

<main class="container mt-5">
    <h1 class="mb-4">Historial de Torques</h1>
    <table class="table table-dark standard-table">
        <thead>
            <tr>
                <th>ID Torque</th>
                <th>Acción</th>
                <th>Fecha</th>
            </tr>
        </thead>
        <tbody>
            <?php while($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($row['torqueID']) ?></td>
                <td><?= htmlspecialchars($row['action']) ?></td>
                <td><?= htmlspecialchars($row['date']) ?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</main>

<?php include 'footer.php'; ?>
