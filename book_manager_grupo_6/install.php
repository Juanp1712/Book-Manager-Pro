<?php
// install.php
// Script de auto-instalación. Ejecutar solo una vez. Detecta si ya existe la BD.

declare(strict_types=1);
session_start();

$baseDir = __DIR__;
$dataDir = $baseDir . DIRECTORY_SEPARATOR . 'data';
$dbFile = $dataDir . DIRECTORY_SEPARATOR . 'book_manager.db';

require_once __DIR__ . '/config/setup.php';

// Si ya existe BD, redirigir
if (is_installed($dbFile)) {
    header('Location: index.php');
    exit;
}

// Intentar crear directorios
$errors = [];

if (!is_dir($dataDir)) {
    if (!@mkdir($dataDir, 0755, true)) {
        $errors[] = "No se pudo crear el directorio data/. Ajusta permisos.";
    }
}

// Crear archivo de BD vacío si no existe
if (!file_exists($dbFile)) {
    $fh = @fopen($dbFile, 'w');
    if ($fh === false) {
        $errors[] = "No se pudo crear el archivo de la base de datos en $dbFile";
    } else {
        fclose($fh);
    }
}

// Conectar y crear tablas
if (empty($errors)) {
    require_once __DIR__ . '/config/database.php';
    try {
        create_tables($pdo);
        create_admin_if_not_exists($pdo);

        // Insertar datos de ejemplo (libros)
        $sample = [
            ['title'=>'Cien años de soledad','author'=>'Gabriel García Márquez','year'=>1967,'genre'=>'Realismo mágico'],
            ['title'=>'El principito','author'=>'Antoine de Saint-Exupéry','year'=>1943,'genre'=>'Infantil'],
            ['title'=>'Clean Code','author'=>'Robert C. Martin','year'=>2008,'genre'=>'Programación']
        ];

        $stmt = $pdo->prepare("INSERT INTO books (title, author, year, genre) VALUES (:title, :author, :year, :genre)");
        foreach ($sample as $b) {
            $stmt->execute([
                ':title' => $b['title'],
                ':author' => $b['author'],
                ':year' => $b['year'],
                ':genre' => $b['genre']
            ]);
        }

        // Intentar ajustar permisos del archivo DB si es posible (no fallar si no se puede)
        @chmod($dbFile, 0644);

        $_SESSION['flash'] = "Instalación completada. Usuario admin / clave123";
        header('Location: index.php');
        exit;
    } catch (Exception $e) {
        $errors[] = "Error durante la instalación: " . $e->getMessage();
    }
}

?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Instalador - Book Manager Pro (grupo6-3pm)</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container">
        <h1>Instalador - Book Manager Pro</h1>
        <?php if ($errors): ?>
            <div class="alert error">
                <h3>Se encontraron errores:</h3>
                <ul>
                <?php foreach ($errors as $err): ?>
                    <li><?= htmlspecialchars($err) ?></li>
                <?php endforeach; ?>
                </ul>
                <p>Corrige los permisos/propietarios y vuelve a ejecutar.</p>
            </div>
        <?php else: ?>
            <p>Instalación en progreso... si ves esta página por mucho tiempo, revisa logs.</p>
        <?php endif; ?>
    </div>
</body>
</html>
