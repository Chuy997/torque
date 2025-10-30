<?php
// /var/www/html/torque/fuera_de_uso.php
require_once __DIR__ . '/includes/bootstrap.php'; // sesión segura + helpers + $conn
require_login('admin'); // Sólo admin puede reactivar

include __DIR__ . '/header.php';

$errors = [];
$ok     = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF
    csrf_verify_or_die($_POST['csrf_token'] ?? null);

    // Entradas
    $torqueID   = trim((string)($_POST['torque_id'] ?? ''));
    $newTorqueS = trim((string)($_POST['new_torque'] ?? ''));
    $reason     = trim((string)($_POST['reason'] ?? ''));

    // Validaciones
    if ($torqueID === '' || !preg_match('/^[A-Za-z0-9._-]{1,50}$/', $torqueID)) {
        $errors[] = 'Torque ID inválido.';
    }
    if ($newTorqueS === '' || !is_numeric($newTorqueS)) {
        $errors[] = 'El nuevo torque debe ser numérico.';
    }
    if ($reason === '' || mb_strlen($reason) > 255) {
        $errors[] = 'Motivo requerido (máx 255).';
    }

    // Verificar existencia y estado actual
    if (empty($errors)) {
        $q = $conn->prepare("SELECT status FROM torques WHERE torqueID = ?");
        $q->bind_param('s', $torqueID);
        $q->execute();
        $res = $q->get_result();
        if ($res->num_rows === 0) {
            $errors[] = 'No existe un torque con ese ID.';
        } else {
            $row = $res->fetch_assoc();
            if (!in_array($row['status'], ['fuera de uso','calibracion fallida'], true)) {
                $errors[] = 'Sólo se pueden reactivar torques en “fuera de uso” o “calibracion fallida”.';
            } else {
                $prev_status = $row['status'];
            }
        }
    }

    // Reactivar
    if (empty($errors)) {
        $newTorque = (float)$newTorqueS;

        $u = $conn->prepare("UPDATE torques SET torque = ?, status = 'activo' WHERE torqueID = ?");
        $u->bind_param('ds', $newTorque, $torqueID);

        try {
            $u->execute();

            // Historial
            $msg = "Reactivado a ACTIVO (venía de '{$prev_status}'). Nuevo torque={$newTorque}. Motivo: {$reason}";
            $h   = $conn->prepare("INSERT INTO history (torqueID, action) VALUES (?, ?)");
            $h->bind_param('ss', $torqueID, $msg);
            $h->execute();

            $ok = 'Torque reactivado correctamente.';
        } catch (mysqli_sql_exception $e) {
            $errors[] = 'No se pudo reactivar el torque.';
        }
    }
}

// Listar torques reactivables
$list = $conn->query("
    SELECT torqueID, torque, status
    FROM torques
    WHERE status IN ('fuera de uso','calibracion fallida')
    ORDER BY torqueID ASC
");
?>
<main class="container mt-5">
    <h1 class="mb-4">Reactivar Torques Fuera de Uso / Calibración Fallida</h1>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger" role="alert">
            <?= htmlspecialchars(implode(' ', $errors), ENT_QUOTES, 'UTF-8') ?>
        </div>
    <?php elseif (!empty($ok)): ?>
        <div class="alert alert-success" role="alert">
            <?= htmlspecialchars($ok, ENT_QUOTES, 'UTF-8') ?>
        </div>
    <?php endif; ?>

    <?php if ($list->num_rows === 0): ?>
        <div class="alert alert-info" role="alert">
            No hay torques en estado “fuera de uso” o “calibración fallida”.
        </div>
    <?php else: ?>
        <form action="" method="POST" autocomplete="off" novalidate>
            <?= csrf_field() ?>
            <div class="form-group">
                <label for="torque_id">Seleccionar Torque ID:</label>
                <select id="torque_id" name="torque_id" class="form-control" required>
                    <?php while ($row = $list->fetch_assoc()): ?>
                        <option value="<?= htmlspecialchars($row['torqueID'], ENT_QUOTES, 'UTF-8') ?>">
                            <?= htmlspecialchars($row['torqueID'], ENT_QUOTES, 'UTF-8') ?>
                            (Torque actual: <?= htmlspecialchars($row['torque'], ENT_QUOTES, 'UTF-8') ?>,
                            Estado: <?= htmlspecialchars($row['status'], ENT_QUOTES, 'UTF-8') ?>)
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="new_torque">Nuevo Torque (Kgf/cm):</label>
                <input type="number" id="new_torque" name="new_torque" step="0.01" class="form-control" required>
            </div>

            <div class="form-group">
                <label for="reason">Motivo:</label>
                <input type="text" id="reason" name="reason" class="form-control" maxlength="255" required>
            </div>

            <button type="submit" class="btn btn-primary">Reactivar</button>
        </form>
    <?php endif; ?>
</main>

<?php include __DIR__ . '/footer.php'; ?>
