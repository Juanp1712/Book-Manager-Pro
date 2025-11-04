<?php
// login.php — con seguridad y validaciones mejoradas
declare(strict_types=1);

// Seguridad general
ini_set('display_errors', '0');
error_reporting(E_ALL);

// Iniciar sesión con configuración segura
session_start([
    'cookie_httponly' => true,
    'cookie_samesite' => 'Strict',
    'use_strict_mode' => true,
]);

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/models/User.php';

$userModel = new User($pdo);
$error = '';

// Generar token CSRF (si no existe)
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// --- Protección contra fuerza bruta ---
$maxAttempts = 5;
$lockoutMinutes = 3;
$ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
$lockKey = "login_lock_$ip";
$attemptKey = "login_attempts_$ip";

// Verificar bloqueo temporal
if (isset($_SESSION[$lockKey]) && time() < $_SESSION[$lockKey]) {
    $remaining = ($_SESSION[$lockKey] - time()) / 60;
    $error = sprintf("Demasiados intentos. Intenta de nuevo en %.1f minutos.", $remaining);
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validar token CSRF
    $token = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'], $token)) {
        $error = 'Token CSRF inválido. Por favor, recarga la página.';
    } else {
        // Limpiar y validar entrada
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        if ($username === '' || $password === '') {
            $error = 'Debes ingresar usuario y contraseña.';
        } elseif (!preg_match('/^[A-Za-z0-9_]{3,30}$/', $username)) {
            $error = 'Usuario inválido: solo letras, números y guiones bajos.';
        } else {
            $user = $userModel->findByUsername($username);
            if ($user && password_verify($password, $user['password'])) {
                // Login correcto → reiniciar intentos
                unset($_SESSION[$attemptKey], $_SESSION[$lockKey]);

                // Regenerar ID de sesión
                session_regenerate_id(true);

                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];

                header('Location: index.php');
                exit;
            } else {
                // Intento fallido
                $_SESSION[$attemptKey] = ($_SESSION[$attemptKey] ?? 0) + 1;
                if ($_SESSION[$attemptKey] >= $maxAttempts) {
                    $_SESSION[$lockKey] = time() + ($lockoutMinutes * 60);
                    $error = "Demasiados intentos fallidos. Bloqueo temporal de {$lockoutMinutes} minutos.";
                } else {
                    $error = 'Credenciales inválidas.';
                }
            }
        }
    }
}

function e($s) {
    return htmlspecialchars((string)$s, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

// Cabeceras seguras
header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('X-XSS-Protection: 1; mode=block');
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Login - Book Manager Pro</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="login-body">
    <div class="login-card">
        <h1>Book Manager Pro</h1>

        <?php if ($error): ?>
            <div class="alert error"><?= e($error) ?></div>
        <?php endif; ?>

        <form method="post" novalidate>
            <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">

            <label for="username">Usuario</label>
            <input id="username" name="username" required minlength="3" maxlength="30"
                   value="<?= e($_POST['username'] ?? '') ?>" 
                   pattern="[A-Za-z0-9_]+" placeholder="Tu usuario...">

            <label for="password">Contraseña</label>
            <input id="password" type="password" name="password" required minlength="4" placeholder="Tu contraseña...">

            <button type="submit" class="btn-login">Iniciar sesión</button>
        </form>

        <div class="footer">© Grupo6-3pm | Book Manager Pro</div>
    </div>
</body>
</html>
