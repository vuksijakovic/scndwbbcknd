<?php

namespace App\Repository;



use App\Service\Database;
use PDO;

class OrderRepository {
    private Database $db;

    public function __construct(Database $db) {
        $this->db = $db;
    }

    public function addOrder(string $items, float $total): bool {
        $stmt = $this->db->getConnection()->prepare(
            'INSERT INTO orders (items, total) VALUES (:items, :total)'
        );

        return $stmt->execute([
            'items' => $items,
            'total' => $total,
        ]);
    }
}
