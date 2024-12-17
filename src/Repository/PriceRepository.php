<?php

namespace App\Repository;

use App\Model\Category;
use App\Model\Price;
use App\Model\Currency;

use App\Service\Database;
use PDO;

class PriceRepository {
    private Database $db;

    public function __construct(Database $db) {
        $this->db = $db;
    }

    public function getByProductId(string $productId): array {
        
        $stmt = $this->db->getConnection()->prepare(
            'SELECT p.id, p.product_id, p.currency_id, p.amount, c.label as currency_label, c.symbol as currency_symbol 
            FROM prices p
            JOIN currencies c ON p.currency_id = c.id
            WHERE p.product_id = :product_id'
        );
       
        $stmt->execute(['product_id' => $productId]);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $prices = array_map(function ($row) {
            $currency = new Currency($row['currency_id'], $row['currency_label'], $row['currency_symbol']);
            $price = new Price($row['id'], $row['product_id'], $row['currency_id'], $row['amount']);
            $price->setCurrency($currency);
            return $price;
        }, $result);

        return $prices;
    }
}
