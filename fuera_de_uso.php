<?php
// /var/www/html/torque/fuera_de_uso.php
require_once __DIR__ . '/includes/bootstrap.php';
require_auth('admin'); // Sólo admin puede reactivar

function h(?string $s): string { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

$pdo = pdo();
$errors = [];
$ok = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF
    if (!isset($_POST['csrf']) || !csrf_validate($_POST['csrf'])) {
        $errors[] = 'Sesión expirada. Vuelve a intentar.';
    }

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
        $q = $pdo->prepare("SELECT status FROM torques WHERE torqueID = ?");
        $q->execute([$torqueID]);
        $row = $q->fetch();
        if (!$row) {
            $errors[] = 'No existe un torque con ese ID.';
        } else {
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

        try {
            $pdo->beginTransaction();

            $u = $pdo->prepare("UPDATE torques SET torque = ?, status = 'activo' WHERE torqueID = ?");
            $u->execute([$newTorque, $torqueID]);

            // Historial
            $msg = "Reactivado a ACTIVO (venía de '{$prev_status}'). Nuevo torque={$newTorque}. Motivo: {$reason}";
            $h   = $pdo->prepare("INSERT INTO history (torqueID, action) VALUES (?, ?)");
            $h->execute([$torqueID, $msg]);

            $pdo->commit();
            $ok = 'Torque reactivado correctamente.';
        } catch (Throwable $e) {
            $pdo->rollBack();
            $errors[] = 'No se pudo reactivar el torque.';
        }
    }
}

// Listar torques reactivables
$list = $pdo->query("
    SELECT torqueID, torque, status
    FROM torques
    WHERE status IN ('fuera de uso','calibracion fallida')
    ORDER BY torqueID ASC
");
$torques = $list->fetchAll(PDO::FETCH_ASSOC);
?>
<?php include __DIR__ . '/header.php'; ?>
<main class="container mt-5">
    <h1 class="mb-4">Reactivar Torques Fuera de Uso / Calibración Fallida</h1>

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

    <?php if (empty($torques)): ?>
        <div class="alert alert-info" role="alert">
            No hay torques en estado “fuera de uso” o “calibración fallida”.
        </div>
    <?php else: ?>
        <form action="" method="POST" autocomplete="off" novalidate>
            <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
            <div class="form-group">
                <label for="torque_id">Seleccionar Torque ID:</label>
                <select id="torque_id" name="torque_id" class="form-control" required>
                    <?php foreach ($torques as $row): ?>
                        <option value="<?= h($row['torqueID']) ?>">
                            <?= h($row['torqueID']) ?>
                            (Torque actual: <?= h($row['torque']) ?>,
                            Estado: <?= h($row['status']) ?>)
                        </option>
                    <?php endforeach; ?>
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
