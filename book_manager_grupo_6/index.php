<?php
// index.php - lista de libros con paginación básica. Requiere login (simple).
declare(strict_types=1);
session_start();
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/models/Book.php';

$bookModel = new Book($pdo);

// Simple check: si no hay usuarios (instalación incompleta) redirige a install
$baseDir = __DIR__;
$dbFile = $baseDir . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'book_manager.db';
if (!file_exists($dbFile) || filesize($dbFile) === 0) {
    header('Location: install.php');
    exit;
}

// Requiere login mínimo
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 6;
$total = $bookModel->countAll();
$totalPages = (int)ceil($total / $perPage);
$offset = ($page - 1) * $perPage;
$books = $bookModel->getAll($perPage, $offset);

function e($s){ return htmlspecialchars((string)$s); }
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Book Manager Pro - Grupo6-3pm</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <script>
        function confirmDelete(id){
            if(confirm('¿Eliminar este libro? Esta acción no se puede deshacer.')) {
                const form = document.createElement('form');
                form.method = 'post';
                form.action = 'delete.php';
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'id';
                input.value = id;
                form.appendChild(input);
                document.body.appendChild(form);
                form.submit();
            }
        }
    </script>
</head>
<body>
<div class="container">
    <header>
        <h1>Book Manager Pro</h1>
        <div class="meta">
            <span>Usuario: <?=e($_SESSION['username'] ?? '---')?></span>
            <a href="add.php" class="btn">Agregar libro</a>
            <a href="logout.php" class="btn danger">Cerrar sesión</a>
        </div>
    </header>

    <?php if ($flash): ?><div class="alert success"><?=e($flash)?></div><?php endif; ?>

    <?php if (empty($books)): ?>
        <p>No hay libros aún.</p>
    <?php else: ?>
        <table class="table">
            <thead>
                <tr><th>Título</th><th>Autor</th><th>Año</th><th>Género</th><th>Acciones</th></tr>
            </thead>
            <tbody>
                <?php foreach ($books as $b): ?>
                    <tr>
                        <td><?=e($b['title'])?></td>
                        <td><?=e($b['author'])?></td>
                        <td><?=e($b['year'])?></td>
                        <td><?=e($b['genre'])?></td>
                        <td>
                            <a class="btn small" href="edit.php?id=<?=e($b['id'])?>">Editar</a>
                            <button class="btn small danger" onclick="confirmDelete(<?= (int)$b['id'] ?>)">Eliminar</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="pagination">
            <?php if ($page > 1): ?>
                <a href="?page=<?= $page-1 ?>">&laquo; Anterior</a>
            <?php endif; ?>
            Página <?= $page ?> de <?= $totalPages ?>
            <?php if ($page < $totalPages): ?>
                <a href="?page=<?= $page+1 ?>">Siguiente &raquo;</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <footer>
        <small>Proyecto Book Manager Pro — Grupo6-3pm</small>
    </footer>
</div>
</body>
</html>
