<?php
// config/database.php
// Devuelve un objeto PDO conectado al archivo SQLite.
// Ajusta la ruta del archivo si lo deseas.

declare(strict_types=1);

$baseDir = dirname(__DIR__); // one level up from config/
$dataDir = $baseDir . DIRECTORY_SEPARATOR . 'data';
$dbFile = $dataDir . DIRECTORY_SEPARATOR . 'book_manager.db';

if (!is_dir($dataDir)) {
    // No crear aquí, install.php se encarga. Pero si falta, intentar crearlo con permisos seguros.
    @mkdir($dataDir, 0755, true);
}

try {
    $dsn = "sqlite:" . $dbFile;
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];
    $pdo = new PDO($dsn, null, null, $options);
} catch (PDOException $e) {
    // Mensaje amigable para desarrollo; en producción loguear y mostrar genérico.
    die("Error al conectar la base de datos: " . htmlspecialchars($e->getMessage()));
}
