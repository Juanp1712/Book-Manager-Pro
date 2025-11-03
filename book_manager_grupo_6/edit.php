<?php
// edit.php
declare(strict_types=1);
session_start();
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/models/Book.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$bookModel = new Book($pdo);
$id = (int)($_GET['id'] ?? 0);
$book = $bookModel->getById($id);
if (!$book) {
    $_SESSION['flash'] = "Libro no encontrado.";
    header('Location: index.php');
    exit;
}

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'title' => trim($_POST['title'] ?? ''),
        'author' => trim($_POST['author'] ?? ''),
        'year' => trim($_POST['year'] ?? ''),
        'genre' => trim($_POST['genre'] ?? ''),
    ];

    if ($data['title'] === '') $errors[] = 'El título es obligatorio.';
    if ($data['author'] === '') $errors[] = 'El autor es obligatorio.';
    if ($data['year'] !== '' && !ctype_digit($data['year'])) $errors[] = 'El año debe ser un número entero.';

    if (empty($errors)) {
        $bookModel->update($id, $data);
        $_SESSION['flash'] = "Libro actualizado.";
        header('Location: index.php');
        exit;
    }
} else {
    $data = $book;
}

function e($s){ return htmlspecialchars((string)$s); }
?>
<!doctype html>
<html lang="es">
<head><meta charset="utf-8"><title>Editar libro</title><link rel="stylesheet" href="assets/css/style.css"></head>
<body>
<div class="container">
    <h1>Editar libro</h1>
    <?php if ($errors): ?><div class="alert error"><ul><?php foreach ($errors as $err) echo '<li>'.e($err).'</li>';?></ul></div><?php endif; ?>
    <form method="post" novalidate>
        <label>Título <input name="title" required value="<?=e($data['title'])?>"></label>
        <label>Autor <input name="author" required value="<?=e($data['author'])?>"></label>
        <label>Año <input name="year" pattern="\d*" value="<?=e($data['year'])?>"></label>
        <label>Género <input name="genre" value="<?=e($data['genre'])?>"></label>
        <div class="actions">
            <button type="submit">Actualizar</button>
            <a href="index.php" class="btn">Cancelar</a>
        </div>
    </form>
</div>
</body>
</html>
