<?php
// models/Book.php
declare(strict_types=1);

class Book {
    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    // 🔹 CREATE
    public function create(array $data): int {
        $stmt = $this->pdo->prepare("
            INSERT INTO books (title, author, year, genre)
            VALUES (:title, :author, :year, :genre)
        ");
        $stmt->execute([
            ':title' => $data['title'],
            ':author' => $data['author'],
            ':year' => $data['year'] ?: null,
            ':genre' => $data['genre'] ?: null
        ]);
        return (int)$this->pdo->lastInsertId();
    }

    // 🔹 READ ALL
    public function getAll(int $limit = 10, int $offset = 0): array {
        $stmt = $this->pdo->prepare("
            SELECT * FROM books ORDER BY created_at DESC
            LIMIT :limit OFFSET :offset
        ");
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 🔹 READ ONE
    public function getById(int $id): ?array {
        $stmt = $this->pdo->prepare("SELECT * FROM books WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $book = $stmt->fetch(PDO::FETCH_ASSOC);
        return $book ?: null;
    }

    // 🔹 UPDATE
    public function update(int $id, array $data): bool {
        $stmt = $this->pdo->prepare("
            UPDATE books
            SET title = :title, author = :author, year = :year, genre = :genre
            WHERE id = :id
        ");
        return $stmt->execute([
            ':title' => $data['title'],
            ':author' => $data['author'],
            ':year' => $data['year'] ?: null,
            ':genre' => $data['genre'] ?: null,
            ':id' => $id
        ]);
    }

    // 🔹 DELETE
    public function delete(int $id): bool {
        $stmt = $this->pdo->prepare("DELETE FROM books WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    // 🔹 COUNT (para paginación)
    public function countAll(): int {
        $stmt = $this->pdo->query("SELECT COUNT(*) FROM books");
        return (int)$stmt->fetchColumn();
    }
}
