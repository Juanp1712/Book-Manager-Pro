<?php
// edit.php
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

// Verificar autenticación
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

// Generar token CSRF si no existe
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$bookModel = new Book($pdo);

// Validar y sanitizar el ID del libro
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if ($id === false || $id === null || $id <= 0) {
    $_SESSION['flash'] = "ID de libro inválido.";
    header('Location: index.php');
    exit;
}

// Obtener el libro
try {
    $book = $bookModel->getById($id);
    if (!$book) {
        $_SESSION['flash'] = "Libro no encontrado.";
        header('Location: index.php');
        exit;
    }
} catch (Exception $e) {
    error_log("Error al obtener libro: " . $e->getMessage());
    $_SESSION['flash'] = "Error al cargar el libro.";
    header('Location: index.php');
    exit;
}

$errors = [];
$data = $book;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verificar token CSRF
    $submittedToken = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'], $submittedToken)) {
        $errors[] = 'Token de seguridad inválido. Por favor, intente nuevamente.';
    } else {
        // Sanitizar y validar entradas
        $data = [
            'title' => trim($_POST['title'] ?? ''),
            'author' => trim($_POST['author'] ?? ''),
            'year' => trim($_POST['year'] ?? ''),
            'genre' => trim($_POST['genre'] ?? ''),
        ];

        // Validaciones del lado del servidor
        if ($data['title'] === '') {
            $errors[] = 'El título es obligatorio.';
        } elseif (mb_strlen($data['title']) > 255) {
            $errors[] = 'El título no puede exceder 255 caracteres.';
        } elseif (!preg_match('/^[\p{L}\p{N}\s\-\.,;:\'\"()!?]+$/u', $data['title'])) {
            $errors[] = 'El título contiene caracteres no permitidos.';
        }

        if ($data['author'] === '') {
            $errors[] = 'El autor es obligatorio.';
        } elseif (mb_strlen($data['author']) > 255) {
            $errors[] = 'El nombre del autor no puede exceder 255 caracteres.';
        } elseif (!preg_match('/^[\p{L}\s\-\.\']+$/u', $data['author'])) {
            $errors[] = 'El nombre del autor contiene caracteres no permitidos.';
        }

        if ($data['year'] !== '') {
            if (!ctype_digit($data['year'])) {
                $errors[] = 'El año debe ser un número entero.';
            } else {
                $yearInt = (int)$data['year'];
                $currentYear = (int)date('Y');
                if ($yearInt < -3000 || $yearInt > $currentYear + 10) {
                    $errors[] = "El año debe estar entre -3000 y " . ($currentYear + 10) . ".";
                }
            }
        }

        if ($data['genre'] !== '') {
            if (mb_strlen($data['genre']) > 100) {
                $errors[] = 'El género no puede exceder 100 caracteres.';
            } elseif (!preg_match('/^[\p{L}\s\-\/]+$/u', $data['genre'])) {
                $errors[] = 'El género contiene caracteres no permitidos.';
            }
        }

        // Si no hay errores, actualizar el libro
        if (empty($errors)) {
            try {
                $bookModel->update($id, $data);
                
                // Regenerar token CSRF después de operación exitosa
                $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                
                $_SESSION['flash'] = "Libro actualizado exitosamente.";
                header('Location: index.php');
                exit;
            } catch (Exception $e) {
                error_log("Error al actualizar libro: " . $e->getMessage());
                $errors[] = 'Ocurrió un error al actualizar el libro. Por favor, intente nuevamente.';
            }
        }
    }
    
    // Regenerar token CSRF si hubo errores
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Función de escape HTML
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
    <title>Editar Libro - Book Manager Pro</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="container">
    <header>
        <h1>Book Manager Pro</h1>
        <div class="meta">
            <span>Usuario: <?=e($_SESSION['username'])?></span>
            <a href="index.php" class="btn">← Volver al inicio</a>
        </div>
    </header>

    <h1>Editar Libro</h1>

    <?php if ($errors): ?>
        <div class="alert error">
            <ul>
                <?php foreach ($errors as $err): ?>
                    <li><?=e($err)?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="post" novalidate autocomplete="off">
        <!-- Token CSRF -->
        <input type="hidden" name="csrf_token" value="<?=e($_SESSION['csrf_token'])?>">
        
        <label>
            Título *
            <input 
                type="text" 
                name="title" 
                required 
                maxlength="255"
                value="<?=e($data['title'])?>"
                placeholder="Ej: Cien años de soledad"
                autofocus
                pattern="[\p{L}\p{N}\s\-\.,;:'&quot;()!?]+"
            >
        </label>

        <label>
            Autor *
            <input 
                type="text" 
                name="author" 
                required 
                maxlength="255"
                value="<?=e($data['author'])?>"
                placeholder="Ej: Gabriel García Márquez"
                pattern="[\p{L}\s\-\.']+"
            >
        </label>

        <label>
            Año de publicación
            <input 
                type="text" 
                name="year" 
                maxlength="5"
                pattern="-?\d{1,4}"
                value="<?=e($data['year'])?>"
                placeholder="Ej: 1967"
                inputmode="numeric"
            >
        </label>

        <label>
            Género
            <input 
                type="text" 
                name="genre" 
                maxlength="100"
                value="<?=e($data['genre'])?>"
                placeholder="Ej: Realismo mágico"
                pattern="[\p{L}\s\-\/]+"
            >
        </label>

        <div class="actions">
            <button type="submit">💾 Actualizar libro</button>
            <a href="index.php" class="btn">Cancelar</a>
        </div>
    </form>

    <footer>
        <small>Proyecto Book Manager Pro — Grupo6-3pm</small>
    </footer>
</div>
</body>
</html>