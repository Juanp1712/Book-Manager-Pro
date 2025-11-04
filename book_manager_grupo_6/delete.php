<?php
// delete.php - solo vía POST, con confirmación en cliente
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

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/models/Book.php';

// Verificar autenticación completa
if (!isset($_SESSION['user_id']) || !isset($_SESSION['username'])) {
    session_unset();
    session_destroy();
    header('Location: login.php');
    exit;
}

// Verificar timeout de sesión (30 minutos)
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 1800)) {
    session_unset();
    session_destroy();
    header('Location: login.php?timeout=1');
    exit;
}
$_SESSION['last_activity'] = time();

// Solo permitir método POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['flash'] = "Método no permitido. Use el formulario correcto.";
    header('Location: index.php');
    exit;
}

// Verificar token CSRF
$submittedToken = $_POST['csrf_token'] ?? '';
if (!isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $submittedToken)) {
    error_log("Intento de CSRF detectado desde IP: " . ($_SERVER['REMOTE_ADDR'] ?? 'desconocida'));
    $_SESSION['flash'] = "Token de seguridad inválido. Por favor, intente nuevamente.";
    header('Location: index.php');
    exit;
}

// Validar y sanitizar ID
$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
if ($id === false || $id === null || $id <= 0) {
    $_SESSION['flash'] = 'ID de libro inválido.';
    header('Location: index.php');
    exit;
}

$bookModel = new Book($pdo);

try {
    // Verificar que el libro existe antes de eliminar
    $book = $bookModel->getById($id);
    
    if (!$book) {
        $_SESSION['flash'] = "El libro no existe o ya fue eliminado.";
        header('Location: index.php');
        exit;
    }
    
    // Guardar título para el mensaje (antes de eliminar)
    $bookTitle = htmlspecialchars($book['title'], ENT_QUOTES, 'UTF-8');
    
    // Intentar eliminar el libro
    $deleted = $bookModel->delete($id);
    
    if ($deleted) {
        $_SESSION['flash'] = "Libro \"$bookTitle\" eliminado exitosamente.";
        error_log("Libro ID $id eliminado por usuario " . $_SESSION['username']);
    } else {
        $_SESSION['flash'] = 'No se pudo eliminar el libro. Intente nuevamente.';
        error_log("Fallo al eliminar libro ID $id");
    }
    
} catch (PDOException $e) {
    // Error de base de datos
    error_log("Error de BD al eliminar libro ID $id: " . $e->getMessage());
    $_SESSION['flash'] = 'Error al eliminar el libro. Por favor, contacte al administrador.';
} catch (Exception $e) {
    // Cualquier otro error
    error_log("Error general al eliminar libro ID $id: " . $e->getMessage());
    $_SESSION['flash'] = 'Ocurrió un error inesperado. Por favor, intente nuevamente.';
}

// Regenerar token CSRF después de la operación
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));

// Redirigir de vuelta al índice
header('Location: index.php');
exit;