<?php
// /var/www/html/torque/calibracion.php
require_once __DIR__ . '/includes/bootstrap.php';
require_auth(); // cualquier usuario logueado puede calibrar

function h(?string $s): string { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

/**
 * Convierte lbf·in a kgf·cm.
 * 1 lbf·in = 1.15195 kgf·cm
 */
function lbf_in_to_kgf_cm(float $v): float {
    return $v * 1.15195;
}

$pdo = pdo();
$errors = [];
$ok = null;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // CSRF
    if (!isset($_POST['csrf']) || !csrf_validate($_POST['csrf'])) {
        $errors[] = 'Sesión expirada. Vuelve a intentar.';
    }

    // Entradas
    $torqueID   = trim((string)($_POST['torque_id'] ?? ''));
    $empleadoID = trim((string)($_POST['empleado_id'] ?? ''));
    $v1s = trim((string)($_POST['valor1'] ?? ''));
    $v2s = trim((string)($_POST['valor2'] ?? ''));
    $v3s = trim((string)($_POST['valor3'] ?? ''));
    $v4s = trim((string)($_POST['valor4'] ?? ''));

    // Validaciones básicas
    if ($torqueID === '' || !preg_match('/^[A-Za-z0-9._-]{1,50}$/', $torqueID)) {
        $errors[] = 'Torque ID inválido.';
    }
    if ($empleadoID === '' || strlen($empleadoID) > 50) {
        $errors[] = 'Número de empleado inválido (máx 50).';
    }
    foreach (['v1s'=>$v1s,'v2s'=>$v2s,'v3s'=>$v3s,'v4s'=>$v4s] as $k=>$val) {
        if ($val === '' || !is_numeric($val)) { 
            $errors[] = 'Todos los valores deben ser numéricos (lbf·in).'; 
            break; 
        }
    }

    // Obtener torque objetivo (Kgf/cm) y verificar que esté ACTIVO
    if (empty($errors)) {
        $q = $pdo->prepare("SELECT torque, status FROM torques WHERE torqueID = ?");
        $q->execute([$torqueID]);
        $row = $q->fetch();
        if (!$row) {
            $errors[] = 'No existe el torque seleccionado.';
        } else {
            if ($row['status'] !== 'activo') {
                $errors[] = 'El torque no está en estado ACTIVO.';
            } else {
                $torque_target_kgfcm = (float)$row['torque']; // DB almacena Kgf/cm
            }
        }
    }

    if (empty($errors)) {
        // Promedio en lbf·in -> convertir a kgf·cm para comparar contra objetivo
        $v1 = (float)$v1s; $v2 = (float)$v2s; $v3 = (float)$v3s; $v4 = (float)$v4s;
        $promedio_lbf_in = ($v1 + $v2 + $v3 + $v4) / 4.0;
        $promedio_kgf_cm = lbf_in_to_kgf_cm($promedio_lbf_in);

        // Tolerancia ±10%
        $tol   = $torque_target_kgfcm * 0.10;
        $low   = $torque_target_kgfcm - $tol;
        $high  = $torque_target_kgfcm + $tol;

        $resultado = ($promedio_kgf_cm < $low || $promedio_kgf_cm > $high) ? 'fuera de tolerancia' : 'aprobado';

        // Intentos fallidos POR torque (en sesión) -> al 3er fallo: pasa a "fuera de uso"
        secure_session_start();
        if (!isset($_SESSION['calib_attempts']) || !is_array($_SESSION['calib_attempts'])) {
            $_SESSION['calib_attempts'] = [];
        }
        if (!isset($_SESSION['calib_attempts'][$torqueID])) {
            $_SESSION['calib_attempts'][$torqueID] = 0;
        }

        $action_msg = '';
        if ($resultado === 'fuera de tolerancia') {
            $_SESSION['calib_attempts'][$torqueID]++;

            if ($_SESSION['calib_attempts'][$torqueID] >= 3) {
                // mover a fuera de uso
                $u = $pdo->prepare("UPDATE torques SET status = 'fuera de uso' WHERE torqueID = ?");
                $u->execute([$torqueID]);

                $action_msg = "Calibración fuera de tolerancia 3 veces. Torque movido a 'fuera de uso'.";
                // reiniciar contador
                $_SESSION['calib_attempts'][$torqueID] = 0;
            } else {
                $action_msg = "Fuera de tolerancia. Intento {$_SESSION['calib_attempts'][$torqueID]} de 3.";
            }
        } else {
            // aprobado -> reiniciar contador
            $_SESSION['calib_attempts'][$torqueID] = 0;
            $action_msg = "Calibración aprobada.";
        }

        // Insertar registro de calibración (guardamos promedio en Kgf/cm)
        try {
            $pdo->beginTransaction();
            $stmt = $pdo->prepare(
                "INSERT INTO calibrations (torqueID, empleadoID, valor1, valor2, valor3, valor4, promedio, resultado)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
            );
            $stmt->execute([
                $torqueID, $empleadoID,
                $v1, $v2, $v3, $v4,
                $promedio_kgf_cm, $resultado
            ]);

            // Historial
            $h = $pdo->prepare("INSERT INTO history (torqueID, action) VALUES (?, ?)");
            $h->execute([$torqueID, $action_msg]);

            $pdo->commit();
            $ok = "Registro guardado. Resultado: {$resultado}. {$action_msg}";
        } catch (Throwable $e) {
            $pdo->rollBack();
            $errors[] = 'Error al guardar la calibración.';
        }
    }
}

// Obtener torques activos para el selector
$list = $pdo->query("SELECT torqueID FROM torques WHERE status = 'activo' ORDER BY torqueID ASC");
$torques = $list->fetchAll(PDO::FETCH_ASSOC);
?>
<?php include __DIR__ . '/header.php'; ?>
<main class="container mt-5">
    <h1 class="mb-4">Calibración de Torque</h1>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger" role="alert">
            <?php foreach ($errors as $err): ?>
                <div><?= h($err) ?></div>
            <?php endforeach; ?>
        </div>
    <?php elseif (!empty($ok)): ?>
        <div class="alert alert-success" role="alert">
            <?= h($ok) ?>
        </div>
    <?php endif; ?>

    <form action="" method="POST" autocomplete="off" novalidate>
        <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
        <div class="form-group">
            <label for="torque_id">Seleccionar Torque ID:</label>
            <select id="torque_id" name="torque_id" class="form-control" required>
                <?php foreach ($torques as $row): ?>
                    <option value="<?= h($row['torqueID']) ?>">
                        <?= h($row['torqueID']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="empleado_id">Número de Empleado:</label>
            <input type="text" id="empleado_id" name="empleado_id" class="form-control" maxlength="50" required>
        </div>

        <div class="form-group">
            <label for="valor1">Valor 1 (lbf·in):</label>
            <input type="number" id="valor1" name="valor1" step="0.01" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="valor2">Valor 2 (lbf·in):</label>
            <input type="number" id="valor2" name="valor2" step="0.01" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="valor3">Valor 3 (lbf·in):</label>
            <input type="number" id="valor3" name="valor3" step="0.01" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="valor4">Valor 4 (lbf·in):</label>
            <input type="number" id="valor4" name="valor4" step="0.01" class="form-control" required>
        </div>

        <button type="submit" class="btn btn-primary">Calibrar</button>
    </form>
</main>

<?php include __DIR__ . '/footer.php'; ?>
