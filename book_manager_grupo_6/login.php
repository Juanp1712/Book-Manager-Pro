<?php
// login.php
declare(strict_types=1);
session_start();
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/models/User.php';

$userModel = new User($pdo);
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $error = 'Ingresa usuario y contraseña.';
    } else {
        $user = $userModel->findByUsername($username);
        if ($user && password_verify($password, $user['password'])) {
            // Autenticado
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            header('Location: index.php');
            exit;
        } else {
            $error = 'Credenciales inválidas.';
        }
    }
}
?>
<!doctype html>
<html lang="es">
<head><meta charset="utf-8"><title>Login - Book Manager</title><link rel="stylesheet" href="assets/css/style.css"></head>
<body>
<div class="container">
    <h1>Login</h1>
    <?php if ($error): ?><div class="alert error"><?=htmlspecialchars($error)?></div><?php endif; ?>
    <form method="post" novalidate>
        <label>Usuario
            <input required name="username" value="<?=htmlspecialchars($_POST['username'] ?? '')?>">
        </label>
        <label>Contraseña
            <input type="password" required name="password">
        </label>
        <button type="submit">Entrar</button>
    </form>
</div>
</body>
</html>
