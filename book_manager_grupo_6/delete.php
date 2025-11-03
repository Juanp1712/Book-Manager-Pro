<?php
// delete.php - solo vía POST, con confirmación en cliente
declare(strict_types=1);
session_start();
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/models/Book.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$id = (int)($_POST['id'] ?? 0);
if ($id <= 0) {
    $_SESSION['flash'] = 'ID inválido.';
    header('Location: index.php');
    exit;
}

$bookModel = new Book($pdo);
$deleted = $bookModel->delete($id);
$_SESSION['flash'] = $deleted ? 'Libro eliminado.' : 'No se pudo eliminar.';
header('Location: index.php');
exit;
