<?php
include 'config.php';
include 'header.php';
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $torqueID = sanitize_input($_POST['torque_id']);
    $password = sanitize_input($_POST['password']);
    
    if ($password == '123456') {
        $updateQuery = "UPDATE Torques SET status = 'activo' WHERE torqueID = ?";
        $stmt = $conn->prepare($updateQuery);
        $stmt->bind_param('s', $torqueID);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            $historyQuery = "INSERT INTO History (torqueID, action) VALUES (?, 'Movido a activo desde fuera de uso')";
            $historyStmt = $conn->prepare($historyQuery);
            $historyStmt->bind_param('s', $torqueID);
            $historyStmt->execute();
            
            echo "Torque movido a activo correctamente.";
        } else {
            echo "No se encontró el torque o no se realizaron cambios.";
        }
    } else {
        echo "Contraseña incorrecta.";
    }
}

function sanitize_input($data) {
    return htmlspecialchars(stripslashes(trim($data)));
}

// Obtener los torques fuera de uso
$query = "SELECT torqueID, torque, SN, status FROM Torques WHERE status = 'fuera de uso'";
$result = $conn->query($query);
?>

<main class="container mt-5">
    <h1 class="mb-4">Torques Fuera de Uso</h1>
    <table class="table table-dark standard-table">
        <thead>
            <tr>
                <th>ID Torque</th>
                <th>Torque (Kgf/cm)</th>
                <th>SN</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
            <?php while($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($row['torqueID']) ?></td>
                <td><?= htmlspecialchars($row['torque']) ?></td>
                <td><?= htmlspecialchars($row['SN']) ?></td>
                <td><?= htmlspecialchars($row['status']) ?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
    <form action="" method="POST">
        <div class="form-group">
            <label for="torque_id">Torque ID:</label>
            <input type="text" id="torque_id" name="torque_id" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="password">Contraseña:</label>
            <input type="password" id="password" name="password" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary">Mover a Activo</button>
    </form>
</main>

<?php include 'footer.php'; ?>
