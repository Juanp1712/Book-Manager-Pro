<?php
// logout.php
declare(strict_types=1);

// Configuración de seguridad
ini_set('display_errors', '0');
error_reporting(E_ALL);

// Iniciar sesión con configuraciones seguras
session_start([
    'cookie_httponly' => true,
    'cookie_samesite' => 'Strict',
    'use_strict_mode' => true
]);

// Verificar si el usuario está autenticado
$isAuthenticated = isset($_SESSION['user_id']) && isset($_SESSION['username']);
$username = $_SESSION['username'] ?? 'Usuario';

// Si no está autenticado, redirigir a login directamente
if (!$isAuthenticated) {
    session_unset();
    session_destroy();
    header('Location: login.php');
    exit;
}

// Generar token CSRF si no existe
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Procesar logout si es POST con token válido
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verificar token CSRF
    $submittedToken = $_POST['csrf_token'] ?? '';
    
    if (hash_equals($_SESSION['csrf_token'], $submittedToken)) {
        // Log del logout
        error_log("Usuario '$username' (ID: {$_SESSION['user_id']}) cerró sesión desde IP: " . ($_SERVER['REMOTE_ADDR'] ?? 'desconocida'));
        
        // Destruir sesión de forma segura
        $_SESSION = [];
        
        // Eliminar cookie de sesión
        if (isset($_COOKIE[session_name()])) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }
        
        // Destruir sesión
        session_destroy();
        
        // Redirigir a login con mensaje
        header('Location: login.php?logged_out=1');
        exit;
    } else {
        // Token CSRF inválido
        error_log("Intento de logout con token CSRF inválido desde IP: " . ($_SERVER['REMOTE_ADDR'] ?? 'desconocida'));
        $error = "Token de seguridad inválido.";
    }
}

function e($s) { 
    return htmlspecialchars((string)$s, ENT_QUOTES | ENT_HTML5, 'UTF-8'); 
}
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-Content-Type-Options" content="nosniff">
    <meta http-equiv="X-Frame-Options" content="DENY">
    <meta http-equiv="X-XSS-Protection" content="1; mode=block">
    <meta name="referrer" content="strict-origin-when-cross-origin">
    <title>Cerrar Sesión - Book Manager Pro</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <script>
    // Prevenir envío múltiple del formulario
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('logoutForm');
        if (form) {
            form.addEventListener('submit', function(e) {
                const submitBtn = this.querySelector('button[type="submit"]');
                if (submitBtn.disabled) {
                    e.preventDefault();
                    return false;
                }
                submitBtn.disabled = true;
                submitBtn.innerHTML = '⏳ Cerrando sesión...';
            });
        }
        
        // Auto-foco en el botón de cerrar sesión
        const logoutBtn = document.querySelector('.btn-logout');
        if (logoutBtn) {
            logoutBtn.focus();
        }
    });
    </script>
</head>
<body>
<div class="logout-container">
    <div class="logout-icon">👋</div>
    
    <h1>¿Cerrar Sesión?</h1>
    
    <p>
        Hola <span class="username"><?= e($username) ?></span>,<br>
        ¿Estás seguro de que deseas cerrar tu sesión?
    </p>
    
    <?php if (isset($error)): ?>
        <div class="error-message">
            <?= e($error) ?>
        </div>
    <?php endif; ?>
    
    <form method="post" id="logoutForm">
        <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">
        
        <div class="logout-buttons">
            <button type="submit" class="btn-logout">
                🔒 Sí, cerrar sesión
            </button>
            <a href="index.php" class="btn-cancel">
                ← Cancelar
            </a>
        </div>
    </form>
    
    <footer class="logout-footer">
        <small>Proyecto Book Manager Pro — Grupo6-3pm</small>
    </footer>
</div>
</body>
</html>