<?php
// models/User.php
declare(strict_types=1);

class User {
    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    // 🔹 READ (por nombre de usuario)
    public function findByUsername(string $username): ?array {
        $stmt = $this->pdo->prepare("
            SELECT id, username, password, email
            FROM users
            WHERE username = :username
            LIMIT 1
        ");
        $stmt->execute([':username' => $username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        return $user ?: null;
    }

    // 🔹 CREATE
    public function create(array $data): int {
        $stmt = $this->pdo->prepare("
            INSERT INTO users (username, password, email)
            VALUES (:username, :password, :email)
        ");
        $stmt->execute([
            ':username' => $data['username'],
            ':password' => password_hash($data['password'], PASSWORD_DEFAULT),
            ':email' => $data['email'] ?? null
        ]);
        return (int)$this->pdo->lastInsertId();
    }

    // 🔹 UPDATE (nombre, contraseña y correo)
    public function update(int $id, array $data): bool {
        $fields = [];
        $params = [':id' => $id];

        if (!empty($data['username'])) {
            $fields[] = 'username = :username';
            $params[':username'] = $data['username'];
        }
        if (!empty($data['password'])) {
            $fields[] = 'password = :password';
            $params[':password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }
        if (!empty($data['email'])) {
            $fields[] = 'email = :email';
            $params[':email'] = $data['email'];
        }

        if (empty($fields)) {
            return false; // nada que actualizar
        }

        $sql = "UPDATE users SET " . implode(', ', $fields) . " WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($params);
    }

    // 🔹 DELETE
    public function delete(int $id): bool {
        $stmt = $this->pdo->prepare("DELETE FROM users WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    // 🔹 EXISTS (verificar si ya existe un usuario)
    public function exists(string $username): bool {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM users WHERE username = :username");
        $stmt->execute([':username' => $username]);
        return (bool)$stmt->fetchColumn();
    }
}
