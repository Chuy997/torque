<?php
// /var/www/html/torque/agregar_torque.php
require_once __DIR__ . '/includes/bootstrap.php'; // sesión segura + helpers + $conn
require_login('admin'); // sólo Admin puede dar de alta

include __DIR__ . '/header.php';

// Configuración de subida
const MAX_BYTES = 3 * 1024 * 1024; // 3 MB
$allowed_exts  = ['jpg','jpeg','png'];
$allowed_mimes = ['image/jpeg','image/png'];

$errors = [];
$notice = null;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // CSRF
    csrf_verify_or_die($_POST['csrf_token'] ?? null);

    // Sanitizar
    $torqueID  = trim((string)($_POST['torque_id'] ?? ''));
    $fechaAlta = trim((string)($_POST['fecha_alta'] ?? ''));
    $torque    = (string)($_POST['torque'] ?? '');
    $SN        = trim((string)($_POST['sn'] ?? ''));

    // Validaciones básicas
    if ($torqueID === '' || !preg_match('/^[A-Za-z0-9._-]{1,50}$/', $torqueID)) {
        $errors[] = 'Torque ID inválido (usa letras, números, . _ - ; máx 50).';
    }
    if ($fechaAlta === '' || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $fechaAlta)) {
        $errors[] = 'Fecha de alta inválida.';
    }
    if ($torque === '' || !is_numeric($torque)) {
        $errors[] = 'Torque numérico requerido.';
    } else {
        $torque = (float)$torque;
    }
    if ($SN === '' || strlen($SN) > 50) {
        $errors[] = 'SN requerido (máx 50).';
    }

    // Validación de archivo
    if (!isset($_FILES['foto']) || ($_FILES['foto']['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        $errors[] = 'Debe adjuntar una foto.';
    } else {
        $f = $_FILES['foto'];
        if ($f['error'] !== UPLOAD_ERR_OK) {
            $errors[] = 'Error al subir la foto (código '.$f['error'].').';
        } elseif ($f['size'] > MAX_BYTES) {
            $errors[] = 'La foto supera 3MB.';
        } else {
            // Verificación por MIME real
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mime  = $finfo->file($f['tmp_name']);
            if (!in_array($mime, $allowed_mimes, true)) {
                $errors[] = 'Formato de imagen no permitido (usa JPG o PNG).';
            }
        }
    }

    // Verificar duplicado de torqueID
    if (empty($errors)) {
        $check = $conn->prepare("SELECT 1 FROM torques WHERE torqueID = ?");
        $check->bind_param('s', $torqueID);
        $check->execute();
        $exists = $check->get_result()->num_rows > 0;
        if ($exists) {
            $errors[] = 'Ya existe un torque con ese ID.';
        }
    }

    if (empty($errors)) {
        // Asegurar carpeta destino
        $dest_dir_abs = __DIR__ . '/pictures';
        if (!is_dir($dest_dir_abs)) {
            // 0775 para que www-data pueda leer
            if (!mkdir($dest_dir_abs, 0775, true) && !is_dir($dest_dir_abs)) {
                $errors[] = 'No se pudo crear el directorio de imágenes.';
            }
        }

        if (empty($errors)) {
            // Extensión según MIME
            $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
            if ($mime === 'image/jpeg') $ext = 'jpg';
            if ($mime === 'image/png')  $ext = 'png';
            if (!in_array($ext, $allowed_exts, true)) {
                $errors[] = 'Extensión no permitida.';
            } else {
                // Nombre estandarizado <torqueID>.<ext>
                $dest_rel = 'pictures/' . $torqueID . '.' . $ext;               // para guardar en DB
                $dest_abs = __DIR__ . '/' . $dest_rel;                           // ruta absoluta para mover

                // Mover archivo
                if (!move_uploaded_file($_FILES['foto']['tmp_name'], $dest_abs)) {
                    $errors[] = 'No se pudo guardar la imagen en el servidor.';
                } else {
                    // Permisos razonables
                    @chmod($dest_abs, 0644);

                    // Insert en BD
                    $stmt = $conn->prepare("INSERT INTO torques (torqueID, fechaAlta, foto, torque, SN) VALUES (?, ?, ?, ?, ?)");
                    $stmt->bind_param('sssds', $torqueID, $fechaAlta, $dest_rel, $torque, $SN);
                    try {
                        $stmt->execute();
                        // Redirigir a inicio
                        header("Location: /torque/index.php");
                        exit();
                    } catch (mysqli_sql_exception $e) {
                        // Si falla el INSERT, borra la imagen para no dejar residuo
                        @unlink($dest_abs);
                        $errors[] = 'Error al insertar en base de datos.';
                    }
                }
            }
        }
    }
}
?>
<main class="container mt-5">
    <h1 class="mb-4">Agregar Torque</h1>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger" role="alert">
            <?= htmlspecialchars(implode(' ', $errors), ENT_QUOTES, 'UTF-8') ?>
        </div>
    <?php elseif (!empty($notice)): ?>
        <div class="alert alert-info" role="alert">
            <?= htmlspecialchars($notice, ENT_QUOTES, 'UTF-8') ?>
        </div>
    <?php endif; ?>

    <form action="" method="POST" enctype="multipart/form-data" autocomplete="off" novalidate>
        <?= csrf_field() ?>
        <div class="form-group">
            <label for="torque_id">Torque ID:</label>
            <input type="text" id="torque_id" name="torque_id" class="form-control" maxlength="50" required>
        </div>
        <div class="form-group">
            <label for="fecha_alta">Fecha de Alta:</label>
            <input type="date" id="fecha_alta" name="fecha_alta" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="foto">Foto (JPG/PNG, máx 3MB):</label>
            <input type="file" id="foto" name="foto" class="form-control" accept=".jpg,.jpeg,.png,image/jpeg,image/png" required>
        </div>
        <div class="form-group">
            <label for="torque">Torque (Kgf/cm):</label>
            <input type="number" id="torque" name="torque" step="0.01" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="sn">SN:</label>
            <input type="text" id="sn" name="sn" class="form-control" maxlength="50" required>
        </div>
        <button type="submit" class="btn btn-primary">Agregar</button>
    </form>
</main>

<?php include __DIR__ . '/footer.php'; ?>
