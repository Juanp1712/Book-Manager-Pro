<?php
// config/setup.php
// Funciones utilitarias para la instalación y chequeos.

declare(strict_types=1);

function is_installed(string $dbPath): bool {
    return file_exists($dbPath) && filesize($dbPath) > 0;
}

function create_tables(PDO $pdo): void {
    $pdo->beginTransaction();

    $pdo->exec(<<<SQL
CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    username TEXT UNIQUE NOT NULL,
    password TEXT NOT NULL,
    email TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);
SQL
    );

    $pdo->exec(<<<SQL
CREATE TABLE IF NOT EXISTS books (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    title TEXT NOT NULL,
    author TEXT NOT NULL,
    year INTEGER,
    genre TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);
SQL
    );

    $pdo->commit();
}

function create_admin_if_not_exists(PDO $pdo): void {
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = :username");
    $stmt->execute([':username' => 'admin']);
    $exists = $stmt->fetchColumn();

    if (!$exists) {
        $hash = password_hash('clave123', PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (username, password, email) VALUES (:u, :p, :e)");
        $stmt->execute([
            ':u' => 'admin',
            ':p' => $hash,
            ':e' => 'admin@biblioteca.edu'
        ]);
    }
}
