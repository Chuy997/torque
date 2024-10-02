<?php
include 'config.php';
include 'header.php';
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// Consulta para obtener las calibraciones más recientes
$query = "SELECT t.torqueID, t.foto, c.promedio, c.resultado, c.fechaCalibracion 
          FROM Torques t
          JOIN Calibrations c ON t.torqueID = c.torqueID
          WHERE c.calibrationID = (SELECT MAX(calibrationID) FROM Calibrations WHERE torqueID = t.torqueID)
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
            <?php while($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($row['torqueID']) ?></td>
                <td><img src="<?= htmlspecialchars($row['foto']) ?>" alt="Foto de <?= htmlspecialchars($row['torqueID']) ?>" style="width: 100px; height: auto;"></td>
                <td><?= htmlspecialchars($row['promedio']) ?></td>
                <td><?= htmlspecialchars($row['resultado']) ?></td>
                <td><?= htmlspecialchars($row['fechaCalibracion']) ?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</main>

<?php include 'footer.php'; ?>
