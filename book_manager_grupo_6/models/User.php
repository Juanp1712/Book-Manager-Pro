<?php
// models/User.php
declare(strict_types=1);

class User {
    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function findByUsername(string $username): ?array {
        $stmt = $this->pdo->prepare("SELECT id, username, password, email FROM users WHERE username = :u LIMIT 1");
        $stmt->execute([':u' => $username]);
        $row = $stmt->fetch();
        return $row ?: null;
    }
}