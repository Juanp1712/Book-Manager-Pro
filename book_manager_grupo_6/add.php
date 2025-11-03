<?php
// add.php
declare(strict_types=1);
session_start();
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/models/Book.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$bookModel = new Book($pdo);
$errors = [];
$old = ['title'=>'','author'=>'','year'=>'','genre'=>''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old['title'] = trim($_POST['title'] ?? '');
    $old['author'] = trim($_POST['author'] ?? '');
    $old['year'] = trim($_POST['year'] ?? '');
    $old['genre'] = trim($_POST['genre'] ?? '');

    // Validación servidor
    if ($old['title'] === '') $errors[] = 'El título es obligatorio.';
    if ($old['author'] === '') $errors[] = 'El autor es obligatorio.';
    if ($old['year'] !== '' && !ctype_digit($old['year'])) $errors[] = 'El año debe ser un número entero.';

    if (empty($errors)) {
        $id = $bookModel->create($old);
        $_SESSION['flash'] = "Libro agregado (id: $id).";
        header('Location: index.php');
        exit;
    }
}

function e($s){ return htmlspecialchars((string)$s); }
?>
<!doctype html>
<html lang="es">
<head><meta charset="utf-8"><title>Agregar libro</title><link rel="stylesheet" href="assets/css/style.css"></head>
<body>
<div class="container">
    <h1>Agregar libro</h1>
    <?php if ($errors): ?><div class="alert error"><ul><?php foreach ($errors as $err) echo '<li>'.e($err).'</li>';?></ul></div><?php endif; ?>
    <form method="post" novalidate>
        <label>Título <input name="title" required value="<?=e($old['title'])?>"></label>
        <label>Autor <input name="author" required value="<?=e($old['author'])?>"></label>
        <label>Año <input name="year" pattern="\d*" value="<?=e($old['year'])?>"></label>
        <label>Género <input name="genre" value="<?=e($old['genre'])?>"></label>
        <div class="actions">
            <button type="submit">Guardar</button>
            <a href="index.php" class="btn">Cancelar</a>
        </div>
    </form>
</div>
</body>
</html>
