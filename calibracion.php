<?php
include 'config.php';
include 'header.php';
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

function convertToKgfCm($value) {
    return $value * 1.15195;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $torqueID = sanitize_input($_POST['torque_id']);
    $empleadoID = sanitize_input($_POST['empleado_id']);
    $valor1 = sanitize_input($_POST['valor1']);
    $valor2 = sanitize_input($_POST['valor2']);
    $valor3 = sanitize_input($_POST['valor3']);
    $valor4 = sanitize_input($_POST['valor4']);
    
    $promedio = ($valor1 + $valor2 + $valor3 + $valor4) / 4;
    $promedioKgfCm = convertToKgfCm($promedio);
    
    $query = "SELECT torque FROM Torques WHERE torqueID = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('s', $torqueID);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    $torque = $row['torque'];
    $tolerance = $torque * 0.10;
    
    $resultado = '';
    $action = '';

    if ($promedioKgfCm < ($torque - $tolerance) || $promedioKgfCm > ($torque + $tolerance)) {
        $resultado = 'fuera de tolerancia';
        
        // Incrementar el número de intentos de calibración fallidos
        if (!isset($_SESSION['calibration_attempts'])) {
            $_SESSION['calibration_attempts'] = 0;
        }
        $_SESSION['calibration_attempts']++;
        
        if ($_SESSION['calibration_attempts'] >= 3) {
            // Mover a fuera de uso después de 3 intentos fallidos
            $updateQuery = "UPDATE Torques SET status = 'fuera de uso' WHERE torqueID = ?";
            $updateStmt = $conn->prepare($updateQuery);
            $updateStmt->bind_param('s', $torqueID);
            $updateStmt->execute();
            
            $action = "Calibración fallida 3 veces. Torque movido a fuera de uso.";
            echo "Calibración fallida 3 veces. Torque movido a fuera de uso.";
            unset($_SESSION['calibration_attempts']);
        } else {
            $action = "Fuera de tolerancia. Intento " . $_SESSION['calibration_attempts'] . " de 3.";
            echo $action;
        }
    } else {
        $resultado = 'aprobado';
        $action = "Calibración aprobada.";
        unset($_SESSION['calibration_attempts']);
    }
    
    $calibrationQuery = "INSERT INTO Calibrations (torqueID, empleadoID, valor1, valor2, valor3, valor4, promedio, resultado) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $calibrationStmt = $conn->prepare($calibrationQuery);
    $calibrationStmt->bind_param('ssssssds', $torqueID, $empleadoID, $valor1, $valor2, $valor3, $valor4, $promedioKgfCm, $resultado);
    $calibrationStmt->execute();

    // Registrar la acción en el historial
    $historyQuery = "INSERT INTO History (torqueID, action) VALUES (?, ?)";
    $historyStmt = $conn->prepare($historyQuery);
    $historyStmt->bind_param('ss', $torqueID, $action);
    $historyStmt->execute();
    
    header("Location: calibracion.php");
    exit();
}

function sanitize_input($data) {
    return htmlspecialchars(stripslashes(trim($data)));
}

// Obtener los torques activos para mostrarlos en el formulario
$query = "SELECT torqueID FROM Torques WHERE status = 'activo'";
$result = $conn->query($query);
?>

<main class="container mt-5">
    <h1 class="mb-4">Calibración de Torque</h1>
    <form action="" method="POST">
        <div class="form-group">
            <label for="torque_id">Seleccionar Torque ID:</label>
            <select id="torque_id" name="torque_id" class="form-control" required>
                <?php while($row = $result->fetch_assoc()): ?>
                <option value="<?= htmlspecialchars($row['torqueID']) ?>">
                    <?= htmlspecialchars($row['torqueID']) ?>
                </option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="form-group">
            <label for="empleado_id">Número de Empleado:</label>
            <input type="text" id="empleado_id" name="empleado_id" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="valor1">Valor 1 (lbf/in):</label>
            <input type="number" id="valor1" name="valor1" step="0.01" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="valor2">Valor 2 (lbf/in):</label>
            <input type="number" id="valor2" name="valor2" step="0.01" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="valor3">Valor 3 (lbf/in):</label>
            <input type="number" id="valor3" name="valor3" step="0.01" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="valor4">Valor 4 (lbf/in):</label>
            <input type="number" id="valor4" name="valor4" step="0.01" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary">Calibrar</button>
    </form>
</main>

<?php include 'footer.php'; ?>
