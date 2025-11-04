<?php
// index.php - lista de libros con paginación básica. Requiere login (simple).
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

// Regenerar ID de sesión periódicamente para prevenir session fixation
if (!isset($_SESSION['last_regeneration'])) {
    $_SESSION['last_regeneration'] = time();
} elseif (time() - $_SESSION['last_regeneration'] > 300) { // cada 5 minutos
    session_regenerate_id(true);
    $_SESSION['last_regeneration'] = time();
}

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/models/Book.php';

// Verificar que la base de datos existe
$baseDir = __DIR__;
$dbFile = $baseDir . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'book_manager.db';
if (!file_exists($dbFile) || filesize($dbFile) === 0) {
    header('Location: install.php');
    exit;
}

// Requiere login - Verificación mejorada
if (!isset($_SESSION['user_id']) || !isset($_SESSION['username'])) {
    // Limpiar sesión comprometida
    session_unset();
    session_destroy();
    header('Location: login.php');
    exit;
}

// Verificar timeout de sesión (30 minutos de inactividad)
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 1800)) {
    session_unset();
    session_destroy();
    header('Location: login.php?timeout=1');
    exit;
}
$_SESSION['last_activity'] = time();

$bookModel = new Book($pdo);

// Obtener y limpiar mensaje flash
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

// Validación y sanitización de entrada para paginación
$page = 1;
if (isset($_GET['page'])) {
    $pageInput = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT);
    if ($pageInput !== false && $pageInput > 0) {
        $page = $pageInput;
    }
}

// Configuración de paginación con límites
$perPage = 6;
$maxPerPage = 100; // Límite máximo para prevenir ataques de recursos
$perPage = min($perPage, $maxPerPage);

try {
    $total = $bookModel->countAll();
    $totalPages = (int)ceil($total / $perPage);
    
    // Validar que la página esté en rango
    if ($page > $totalPages && $totalPages > 0) {
        $page = $totalPages;
    }
    
    $offset = ($page - 1) * $perPage;
    $books = $bookModel->getAll($perPage, $offset);
} catch (Exception $e) {
    // Log del error (en producción esto debería ir a un archivo de log)
    error_log("Error en index.php: " . $e->getMessage());
    $books = [];
    $total = 0;
    $totalPages = 0;
}

// Función de escape HTML mejorada
function e($s) { 
    return htmlspecialchars((string)$s, ENT_QUOTES | ENT_HTML5, 'UTF-8'); 
}

// Generar token CSRF para el formulario de eliminación
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrfToken = $_SESSION['csrf_token'];
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
    <title>Book Manager Pro - Grupo6-3pm</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <script>
        // Prevenir XSS en confirmación de eliminación
        function confirmDelete(id, title) {
            // Validar que id sea un número
            id = parseInt(id);
            if (isNaN(id) || id <= 0) {
                alert('ID inválido');
                return;
            }
            
            // Escapar el título para prevenir XSS
            const safeTitle = title.replace(/[<>]/g, '');
            
            if(confirm('¿Eliminar el libro "' + safeTitle + '"?\nEsta acción no se puede deshacer.')) {
                const form = document.createElement('form');
                form.method = 'post';
                form.action = 'delete.php';
                
                // Agregar token CSRF
                const csrfInput = document.createElement('input');
                csrfInput.type = 'hidden';
                csrfInput.name = 'csrf_token';
                csrfInput.value = '<?= e($csrfToken) ?>';
                form.appendChild(csrfInput);
                
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
            <span>Usuario: <?=e($_SESSION['username'])?></span>
            <a href="add.php" class="btn">Agregar libro</a>
            <a href="logout.php" class="btn danger">Cerrar sesión</a>
        </div>
    </header>

    <?php if ($flash): ?>
        <div class="alert success"><?=e($flash)?></div>
    <?php endif; ?>

    <?php if (empty($books)): ?>
        <p>No hay libros aún. <a href="add.php">Agregar el primero</a></p>
    <?php else: ?>
        <table class="table">
            <thead>
                <tr>
                    <th>TÍTULO</th>
                    <th>AUTOR</th>
                    <th>AÑO</th>
                    <th>GÉNERO</th>
                    <th>ACCIONES</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($books as $b): ?>
                    <?php
                    // Validación adicional de datos
                    $bookId = filter_var($b['id'] ?? 0, FILTER_VALIDATE_INT);
                    if ($bookId === false || $bookId <= 0) continue;
                    
                    $title = e($b['title'] ?? '');
                    $author = e($b['author'] ?? '');
                    $year = e($b['year'] ?? '');
                    $genre = e($b['genre'] ?? '');
                    ?>
                    <tr>
                        <td data-label="Título"><?= $title ?></td>
                        <td data-label="Autor"><?= $author ?></td>
                        <td data-label="Año"><?= $year ?></td>
                        <td data-label="Género"><?= $genre ?></td>
                        <td>
                            <a class="btn small" href="edit.php?id=<?= $bookId ?>">Editar</a>
                            <button 
                                class="btn small danger" 
                                onclick="confirmDelete(<?= $bookId ?>, '<?= e($b['title']) ?>')">
                                Eliminar
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <?php if ($totalPages > 1): ?>
        <div class="pagination">
            <?php if ($page > 1): ?>
                <a href="?page=<?= $page - 1 ?>">&laquo; Anterior</a>
            <?php endif; ?>
            
            <span>Página <?= $page ?> de <?= $totalPages ?></span>
            
            <?php if ($page < $totalPages): ?>
                <a href="?page=<?= $page + 1 ?>">Siguiente &raquo;</a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    <?php endif; ?>

    <footer>
        <small>Proyecto Book Manager Pro — Grupo6-3pm</small>
    </footer>
</div>
</body>
</html>