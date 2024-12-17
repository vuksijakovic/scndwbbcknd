<?php

namespace App\Repository;

use App\Model\Attribute;
use App\Service\Database;
use PDO;

class AttributeRepository {
    private Database $db;

    public function __construct(Database $db) {
        $this->db = $db;
    }

    public function createAttribute(array $data): bool {
        $stmt = $this->db->getConnection()->prepare(
            'INSERT INTO attributes (id, value, display_value) 
             VALUES (:id, :value, :display_value)'
        );
        return $stmt->execute([
            'id' => $data['id'],
            'value' => $data['value'],
            'display_value' => $data['display_value'],
        ]);
    }
    public function getAllAttributes(): array {
    $stmt = $this->db->getConnection()->prepare(
        'SELECT id, value, display_value FROM attributes'
    );
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
public function getAttributeById(string $id): ?array {
    $stmt = $this->db->getConnection()->prepare(
        'SELECT id, value, display_value FROM attributes WHERE id = :id'
    );
    $stmt->execute(['id' => $id]);
    return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
}
public function updateAttribute(string $id, array $data): bool {
    $stmt = $this->db->getConnection()->prepare(
        'UPDATE attributes SET value = :value, display_value = :display_value 
         WHERE id = :id'
    );
    return $stmt->execute([
        'id' => $id,
        'value' => $data['value'],
        'display_value' => $data['display_value'],
    ]);
}
public function deleteAttribute(string $id): bool {
    $stmt = $this->db->getConnection()->prepare(
        'DELETE FROM attributes WHERE id = :id'
    );
    return $stmt->execute(['id' => $id]);
}
public function getAllAttributeTypes(): array {
    $stmt = $this->db->getConnection()->query(
        'SELECT id, name, type FROM attribute_types'
    );
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
public function getAttributeTypeById(string $id): ?array {
    $stmt = $this->db->getConnection()->prepare(
        'SELECT id, name, type FROM attribute_types WHERE id = :id'
    );
    $stmt->execute(['id' => $id]);
    return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
}
public function createAttributeType(array $data): bool {
    $stmt = $this->db->getConnection()->prepare(
        'INSERT INTO attribute_types (id, name, type) 
         VALUES (:id, :name, :type)'
    );
    return $stmt->execute([
        'id' => $data['id'],
        'name' => $data['name'],
        'type' => $data['type']
    ]);
}
public function updateAttributeType(string $id, array $data): bool {
    $stmt = $this->db->getConnection()->prepare(
        'UPDATE attribute_types 
         SET name = :name, type = :type 
         WHERE id = :id'
    );
    return $stmt->execute([
        'id' => $id,
        'name' => $data['name'],
        'type' => $data['type']
    ]);
}
public function deleteAttributeType(string $id): bool {
    $stmt = $this->db->getConnection()->prepare(
        'DELETE FROM attribute_types WHERE id = :id'
    );
    return $stmt->execute(['id' => $id]);
}
public function addAttributeSets(string $productId, string $attributeTypeId, array $attributeIds): bool {
    $stmt = $this->db->getConnection()->prepare(
        'INSERT INTO attribute_sets (product_id, attribute_type_id, attribute_id) 
         VALUES (:product_id, :attribute_type_id, :attribute_id)'
    );

    foreach ($attributeIds as $attributeId) {
        $stmt->execute([
            'product_id' => $productId,
            'attribute_type_id' => $attributeTypeId,
            'attribute_id' => $attributeId
        ]);
    }

    return true;
}
public function deleteAttributeSets(string $productId, string $attributeTypeId): bool {
    $stmt = $this->db->getConnection()->prepare(
        'DELETE FROM attribute_sets 
         WHERE product_id = :product_id AND attribute_type_id = :attribute_type_id'
    );

    return $stmt->execute([
        'product_id' => $productId,
        'attribute_type_id' => $attributeTypeId
    ]);
}

    
}
