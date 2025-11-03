<?php
// models/Book.php
declare(strict_types=1);

class Book {
    private PDO $pdo;
    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function countAll(): int {
        $stmt = $this->pdo->query("SELECT COUNT(*) FROM books");
        return (int)$stmt->fetchColumn();
    }

    public function getAll(int $limit, int $offset): array {
        $stmt = $this->pdo->prepare("SELECT * FROM books ORDER BY created_at DESC LIMIT :l OFFSET :o");
        $stmt->bindValue(':l', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':o', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getById(int $id): ?array {
        $stmt = $this->pdo->prepare("SELECT * FROM books WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function create(array $data): int {
        $stmt = $this->pdo->prepare("INSERT INTO books (title, author, year, genre) VALUES (:title, :author, :year, :genre)");
        $stmt->execute([
            ':title' => $data['title'],
            ':author' => $data['author'],
            ':year' => $data['year'] ?: null,
            ':genre' => $data['genre'] ?: null
        ]);
        return (int)$this->pdo->lastInsertId();
    }

    public function update(int $id, array $data): bool {
        $stmt = $this->pdo->prepare("UPDATE books SET title = :title, author = :author, year = :year, genre = :genre WHERE id = :id");
        return $stmt->execute([
            ':title' => $data['title'],
            ':author' => $data['author'],
            ':year' => $data['year'] ?: null,
            ':genre' => $data['genre'] ?: null,
            ':id' => $id
        ]);
    }

    public function delete(int $id): bool {
        $stmt = $this->pdo->prepare("DELETE FROM books WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }
}
