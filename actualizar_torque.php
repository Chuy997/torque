<?php
include 'config.php';
include 'header.php';
session_start();

if (!isset($_SESSION['username']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $torqueID = sanitize_input($_POST['torque_id']);
    $status = sanitize_input($_POST['status']);
    $reason = sanitize_input($_POST['reason']);
    
    $newTorque = null;
    if (isset($_POST['new_torque']) && !empty($_POST['new_torque'])) {
        $newTorque = sanitize_input($_POST['new_torque']);
    }
    
    if ($status == 'activo' && $newTorque === null) {
        echo "Debe proporcionar un nuevo valor de torque para activar el torque.";
    } else {
        if ($status == 'fuera de uso' || $status == 'calibracion fallida') {
            $updateQuery = "UPDATE Torques SET status = ? WHERE torqueID = ?";
            $stmt = $conn->prepare($updateQuery);
            $stmt->bind_param('ss', $status, $torqueID);
        } else {
            $updateQuery = "UPDATE Torques SET torque = ?, status = ? WHERE torqueID = ?";
            $stmt = $conn->prepare($updateQuery);
            $stmt->bind_param('dss', $newTorque, $status, $torqueID);
        }
        $stmt->execute();
        
        if ($stmt->affected_rows > 0) {
            $historyQuery = "INSERT INTO History (torqueID, action) VALUES (?, ?)";
            $historyStmt = $conn->prepare($historyQuery);
            $action = "Estado cambiado a $status. Motivo: $reason";
            if ($newTorque !== null) {
                $action = "Torque actualizado a $newTorque, estado cambiado a $status. Motivo: $reason";
            }
            $historyStmt->bind_param('ss', $torqueID, $action);
            $historyStmt->execute();
            
            echo "Torque actualizado correctamente.";
        } else {
            echo "No se encontró el torque o no se realizaron cambios.";
        }
    }
}

function sanitize_input($data) {
    return htmlspecialchars(stripslashes(trim($data)));
}

// Obtener los torques activos para mostrarlos en el formulario
$query = "SELECT torqueID, torque, status FROM Torques WHERE status = 'activo'";
$result = $conn->query($query);
?>

<main class="container mt-5">
    <h1 class="mb-4">Actualizar Torque</h1>
    <form action="" method="POST">
        <div class="form-group">
            <label for="torque_id">Seleccionar Torque ID:</label>
            <select id="torque_id" name="torque_id" class="form-control" required>
                <?php while($row = $result->fetch_assoc()): ?>
                <option value="<?= htmlspecialchars($row['torqueID']) ?>">
                    <?= htmlspecialchars($row['torqueID']) ?> (Torque Actual: <?= htmlspecialchars($row['torque']) ?>, Estado: <?= htmlspecialchars($row['status']) ?>)
                </option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="form-group">
            <label for="new_torque">Nuevo Torque (Kgf/cm):</label>
            <input type="number" id="new_torque" name="new_torque" step="0.01" class="form-control">
        </div>
        <div class="form-group">
            <label for="status">Nuevo Estado:</label>
            <select id="status" name="status" class="form-control" required>
                <option value="activo">Activo</option>
                <option value="fuera de uso">Fuera de Uso</option>
                <option value="calibracion fallida">Calibración Fallida</option>
            </select>
        </div>
        <div class="form-group">
            <label for="reason">Motivo:</label>
            <input type="text" id="reason" name="reason" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary">Actualizar</button>
    </form>
</main>

<?php include 'footer.php'; ?>
