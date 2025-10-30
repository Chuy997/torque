<?php
// /var/www/html/torque/login.php
require_once __DIR__ . '/includes/bootstrap.php'; // inicia sesión segura, helpers y carga config.php

// Si ya está logueado, envía al inicio
if (is_logged_in()) {
    header("Location: /torque/index.php");
    exit();
}

$error = null;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Verifica CSRF
    csrf_verify_or_die($_POST['csrf_token'] ?? null);

    // Sanea entradas
    $username = htmlspecialchars(trim((string)($_POST['username'] ?? '')), ENT_QUOTES, 'UTF-8');
    $password = (string)($_POST['password'] ?? '');

    // Consulta segura (tabla en minúsculas)
    $query = "SELECT userID, username, password, role FROM users WHERE username = ?";
    $stmt  = $conn->prepare($query);
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res && $res->num_rows === 1) {
        $row = $res->fetch_assoc();
        if (password_verify($password, $row['password'])) {
            // Login correcto: regenerar ID y establecer sesión
            session_regenerate_safe();
            $_SESSION['username'] = $row['username'];
            $_SESSION['role']     = $row['role'];
            $_SESSION['userID']   = (int)$row['userID'];

            header("Location: /torque/index.php");
            exit();
        }
    }

    // Si llega aquí, credenciales inválidas (no revelar cuál falló)
    $error = "Usuario o contraseña inválidos.";
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <style>
        body {
            margin: 0; padding: 0; font-family: sans-serif;
            background: #121212; color: #ffffff;
            display: flex; justify-content: center; align-items: center; height: 100vh;
        }
        .login-box {
            position: absolute; top: 50%; left: 50%; width: 400px; padding: 40px;
            transform: translate(-50%, -50%); background: rgba(0,0,0,.5);
            box-shadow: 0 15px 25px rgba(0, 0, 0, 0.5); border-radius: 10px; text-align: center;
        }
        .login-box h1 { margin: 0 0 30px; color: #ffffff; text-align: center; }
        .form-group { position: relative; margin-bottom: 30px; }
        .form-group input {
            width: 100%; padding: 10px 0; font-size: 16px; color: #ffffff;
            border: none; border-bottom: 1px solid #ffffff; outline: none; background: transparent;
        }
        .form-group label {
            position: absolute; top: 0; left: 0; padding: 10px 0; font-size: 16px; color: #ffffff;
            pointer-events: none; transition: 0.5s;
        }
        .form-group input:focus ~ label, .form-group input:valid ~ label {
            top: -20px; left: 0; color: #03e9f4; font-size: 12px;
        }
        .btn {
            display:inline-block; padding: 10px 20px; background: none; border: none; color: #03e9f4;
            font-size: 16px; text-transform: uppercase; margin-top: 40px; letter-spacing: 4px;
            cursor: pointer; transition: 0.5s; position: relative;
        }
        .btn:hover {
            background: #03e9f4; color: #fff; border-radius: 5px;
            box-shadow: 0 0 5px #03e9f4, 0 0 25px #03e9f4, 0 0 50px #03e9f4, 0 0 100px #03e9f4;
        }
        .btn span { position: absolute; display: block; }
        .btn span:nth-child(1) { top: 0; left: 100%; width: 100%; height: 2px; background: linear-gradient(90deg, transparent, #03e9f4); animation: btn-anim1 1s linear infinite; }
        @keyframes btn-anim1 { 0% { left: 100%; } 50%, 100% { left: 0; } }
        .btn span:nth-child(2) { top: -100%; right: 0; width: 2px; height: 100%; background: linear-gradient(180deg, transparent, #03e9f4); animation: btn-anim2 1s linear infinite; animation-delay: 0.25s; }
        @keyframes btn-anim2 { 0% { top: -100%; } 50%, 100% { top: 0; } }
        .btn span:nth-child(3) { bottom: 0; right: 100%; width: 100%; height: 2px; background: linear-gradient(270deg, transparent, #03e9f4); animation: btn-anim3 1s linear infinite; animation-delay: 0.5s; }
        @keyframes btn-anim3 { 0% { right: 100%; } 50%, 100% { right: 0; } }
        .btn span:nth-child(4) { bottom: -100%; left: 0; width: 2px; height: 100%; background: linear-gradient(360deg, transparent, #03e9f4); animation: btn-anim4 1s linear infinite; animation-delay: 0.75s; }
        @keyframes btn-anim4 { 0% { bottom: -100%; } 50%, 100% { bottom: 0; } }
        .alert { color: #ff4d4d; margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="login-box">
        <h1>Login</h1>
        <?php if (!empty($error)): ?>
            <div class="alert" role="alert"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>
        <form action="" method="POST" autocomplete="off" novalidate>
            <?= csrf_field() /* campo oculto CSRF */ ?>
            <div class="form-group">
                <input type="text" id="username" name="username" required>
                <label for="username">Usuario</label>
            </div>
            <div class="form-group">
                <input type="password" id="password" name="password" required>
                <label for="password">Contraseña</label>
            </div>

            <!-- Botón con efecto existente, ahora usando <button> correctamente -->
            <button type="submit" class="btn">
                <span></span><span></span><span></span><span></span>
                Ingresar
            </button>
        </form>
    </div>
</body>
</html>
