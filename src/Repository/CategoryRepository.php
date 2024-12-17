<?php

namespace App\Repository;

use App\Model\Category;
use App\Service\Database;
use PDO;

class CategoryRepository {
    private Database $db;

    public function __construct(Database $db) {
        $this->db = $db;
    }

    public function getAll(): array {
        $stmt = $this->db->getConnection()->query('SELECT id, name FROM categories');
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map(fn($row) => new Category($row['id'], $row['name']), $result);
    }

    public function getById(int $id): ?Category {
        $stmt = $this->db->getConnection()->prepare('SELECT id, name FROM categories WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ? new Category($row['id'], $row['name']) : null;
    }

    public function create(Category $category): bool {
        $stmt = $this->db->getConnection()->prepare('INSERT INTO categories (name) VALUES (:name)');
        return $stmt->execute(['name' => $category->getName()]);
    }

    public function update(Category $category): bool {
        $stmt = $this->db->getConnection()->prepare(
            'UPDATE categories SET name = :name WHERE id = :id'
        );
        return $stmt->execute([
            'id' => $category->getId(),
            'name' => $category->getName(),
        ]);
    }

    public function delete(int $id): bool {
        $stmt = $this->db->getConnection()->prepare('DELETE FROM categories WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }
}
