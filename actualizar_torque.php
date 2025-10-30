<?php
// /var/www/html/torque/actualizar_torque.php
require_once __DIR__ . '/includes/bootstrap.php'; // sesión segura + helpers + $conn
require_login('admin'); // sólo Admin puede actualizar torques

include __DIR__ . '/header.php';

$errors = [];
$ok     = null;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // CSRF obligatorio
    csrf_verify_or_die($_POST['csrf_token'] ?? null);

    // Entradas
    $torqueID   = trim((string)($_POST['torque_id'] ?? ''));
    $status     = trim((string)($_POST['status'] ?? ''));
    $reason     = trim((string)($_POST['reason'] ?? ''));
    $newTorqueS = trim((string)($_POST['new_torque'] ?? ''));

    // Validaciones
    if ($torqueID === '' || !preg_match('/^[A-Za-z0-9._-]{1,50}$/', $torqueID)) {
        $errors[] = 'Torque ID inválido.';
    }
    $allowed_status = ['activo','fuera de uso','calibracion fallida'];
    if (!in_array($status, $allowed_status, true)) {
        $errors[] = 'Estado inválido.';
    }
    if ($reason === '' || mb_strlen($reason) > 255) {
        $errors[] = 'Motivo requerido (máx 255).';
    }

    $newTorque = null;
    if ($newTorqueS !== '') {
        if (!is_numeric($newTorqueS)) {
            $errors[] = 'El nuevo torque debe ser numérico.';
        } else {
            $newTorque = (float)$newTorqueS;
        }
    }

    // Reglas de negocio
    if ($status === 'activo' && $newTorque === null) {
        $errors[] = 'Para activar se requiere un valor de torque nuevo.';
    }

    // Verificar que el torque exista
    if (empty($errors)) {
        $q = $conn->prepare("SELECT torque, status FROM torques WHERE torqueID = ?");
        $q->bind_param('s', $torqueID);
        $q->execute();
        $res = $q->get_result();
        if ($res->num_rows === 0) {
            $errors[] = 'No existe un torque con ese ID.';
        } else {
            $current = $res->fetch_assoc();
        }
    }

    // Ejecutar cambios
    if (empty($errors)) {
        if ($status === 'fuera de uso' || $status === 'calibracion fallida') {
            // Sólo cambiar estado
            $stmt = $conn->prepare("UPDATE torques SET status = ? WHERE torqueID = ?");
            $stmt->bind_param('ss', $status, $torqueID);
            $action = "Estado cambiado a {$status}. Motivo: {$reason}";
        } else {
            // Activo: requiere nuevo torque
            $stmt = $conn->prepare("UPDATE torques SET torque = ?, status = ? WHERE torqueID = ?");
            $stmt->bind_param('dss', $newTorque, $status, $torqueID);
            $action = "Torque actualizado a {$newTorque}, estado cambiado a {$status}. Motivo: {$reason}";
        }

        try {
            $stmt->execute();

            // Registrar historial
            $h = $conn->prepare("INSERT INTO history (torqueID, action) VALUES (?, ?)");
            $h->bind_param('ss', $torqueID, $action);
            $h->execute();

            $ok = 'Torque actualizado correctamente.';
        } catch (mysqli_sql_exception $e) {
            $errors[] = 'No se pudo actualizar el torque.';
        }
    }
}

// Obtener torques activos para el selector (mantenemos lógica original)
$list = $conn->query("SELECT torqueID, torque, status FROM torques WHERE status = 'activo' ORDER BY torqueID ASC");
?>
<main class="container mt-5">
    <h1 class="mb-4">Actualizar Torque</h1>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger" role="alert">
            <?= htmlspecialchars(implode(' ', $errors), ENT_QUOTES, 'UTF-8') ?>
        </div>
    <?php elseif (!empty($ok)): ?>
        <div class="alert alert-success" role="alert">
            <?= htmlspecialchars($ok, ENT_QUOTES, 'UTF-8') ?>
        </div>
    <?php endif; ?>

    <form action="" method="POST" autocomplete="off" novalidate>
        <?= csrf_field() ?>
        <div class="form-group">
            <label for="torque_id">Seleccionar Torque ID:</label>
            <select id="torque_id" name="torque_id" class="form-control" required>
                <?php while ($row = $list->fetch_assoc()): ?>
                    <label>
                        <option value="<?= htmlspecialchars($row['torqueID'], ENT_QUOTES, 'UTF-8') ?>">
                            <?= htmlspecialchars($row['torqueID'], ENT_QUOTES, 'UTF-8') ?>
                            (Torque Actual: <?= htmlspecialchars($row['torque'], ENT_QUOTES, 'UTF-8') ?>,
                            Estado: <?= htmlspecialchars($row['status'], ENT_QUOTES, 'UTF-8') ?>)
                        </option>
                    </label>
                <?php endwhile; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="new_torque">Nuevo Torque (Kgf/cm):</label>
            <input type="number" id="new_torque" name="new_torque" step="0.01" class="form-control" placeholder="Requerido si cambia a 'activo'">
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
            <input type="text" id="reason" name="reason" class="form-control" maxlength="255" required>
        </div>

        <button type="submit" class="btn btn-primary">Actualizar</button>
    </form>
</main>

<?php include __DIR__ . '/footer.php'; ?>
