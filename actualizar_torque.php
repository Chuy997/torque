<?php
// /var/www/html/torque/actualizar_torque.php
require_once __DIR__ . '/includes/bootstrap.php';
require_auth('admin'); // sólo Admin puede actualizar torques

function h(?string $s): string { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

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
        $q = $pdo->prepare("SELECT torque, status FROM torques WHERE torqueID = ?");
        $q->execute([$torqueID]);
        $current = $q->fetch();
        if (!$current) {
            $errors[] = 'No existe un torque con ese ID.';
        }
    }

    // Ejecutar cambios
    if (empty($errors)) {
        try {
            $pdo->beginTransaction();

            if ($status === 'fuera de uso' || $status === 'calibracion fallida') {
                // Sólo cambiar estado
                $stmt = $pdo->prepare("UPDATE torques SET status = ? WHERE torqueID = ?");
                $stmt->execute([$status, $torqueID]);
                $action = "Estado cambiado a {$status}. Motivo: {$reason}";
            } else {
                // Activo: requiere nuevo torque
                $stmt = $pdo->prepare("UPDATE torques SET torque = ?, status = ? WHERE torqueID = ?");
                $stmt->execute([$newTorque, $status, $torqueID]);
                $action = "Torque actualizado a {$newTorque}, estado cambiado a {$status}. Motivo: {$reason}";
            }

            // Registrar historial
            $h = $pdo->prepare("INSERT INTO history (torqueID, action) VALUES (?, ?)");
            $h->execute([$torqueID, $action]);

            $pdo->commit();
            $ok = 'Torque actualizado correctamente.';
        } catch (Throwable $e) {
            $pdo->rollBack();
            $errors[] = 'No se pudo actualizar el torque.';
        }
    }
}

// Obtener torques activos para el selector
$list = $pdo->query("SELECT torqueID, torque, status FROM torques WHERE status = 'activo' ORDER BY torqueID ASC");
$torques = $list->fetchAll(PDO::FETCH_ASSOC);
?>
<?php include __DIR__ . '/header.php'; ?>
<main class="container mt-5">
    <h1 class="mb-4">Actualizar Torque</h1>

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
                        (Torque Actual: <?= h($row['torque']) ?>,
                        Estado: <?= h($row['status']) ?>)
                    </option>
                <?php endforeach; ?>
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
