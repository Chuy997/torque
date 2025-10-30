<?php
// /var/www/html/torque/includes/bootstrap.php
// NOTA: este archivo debe empezar EXACTAMENTE con "<?php" sin espacios ni BOM.

//////////////////////
// Cabeceras de seguridad (antes de cualquier salida)
//////////////////////
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('Referrer-Policy: no-referrer-when-downgrade');
// CSP moderada; permite CDNs https y estilos/scripts inline existentes
header("Content-Security-Policy: default-src 'self'; script-src 'self' https: 'unsafe-inline'; style-src 'self' https: 'unsafe-inline'; img-src 'self' data: https:; object-src 'none'; base-uri 'self'; frame-ancestors 'self'");

//////////////////////
// Sesión endurecida
//////////////////////
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'domain'   => '',
        'secure'   => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_name('torque_sess');
    session_start();
}

function session_regenerate_safe(): void {
    if (!isset($_SESSION['__last_regen']) || (time() - $_SESSION['__last_regen'] > 300)) {
        session_regenerate_id(true);
        $_SESSION['__last_regen'] = time();
    }
}

//////////////////////
// Helpers de auth
//////////////////////
function is_logged_in(): bool {
    return isset($_SESSION['username'], $_SESSION['role']);
}

/**
 * Para PÁGINAS: redirige a login si no hay sesión.
 */
function require_login(?string $role = null): void {
    if (!is_logged_in()) {
        header('Location: /torque/login.php');
        exit();
    }
    if ($role !== null && $_SESSION['role'] !== $role) {
        http_response_code(403);
        exit('Acceso denegado.');
    }
}

/**
 * Para APIS: NO redirige; responde 401 en JSON si no hay sesión.
 */
function require_login_api(?string $role = null): void {
    if (!is_logged_in()) {
        http_response_code(401);
        header('Content-Type: application/json');
        echo json_encode(['error' => 'No autorizado'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit();
    }
    if ($role !== null && $_SESSION['role'] !== $role) {
        http_response_code(403);
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Acceso denegado'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit();
    }
}


//////////////////////
// CSRF
//////////////////////
function csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}
function csrf_field(): string {
    return '<input type="hidden" name="csrf_token" value="'.htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8').'">';
}
function csrf_verify_or_die(?string $token): void {
    if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], (string)$token)) {
        http_response_code(400);
        exit('CSRF token inválido o faltante.');
    }
}

//////////////////////
// DB config
//////////////////////
require_once __DIR__ . '/../config.php';
// Modo SQL estricto (activar luego si todo estable):
// $conn->query("SET SESSION sql_mode = 'STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION'");
