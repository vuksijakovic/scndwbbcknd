<?php

namespace App\Repository;

use App\Model\Product;
use App\Model\ProductGallery;
use App\Model\AttributeSet;
use App\Model\Attribute;
use App\Model\Price;
use App\Model\Category;
use App\Model\Currency;

use PDO;

class ProductRepository {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function getById(string $productId): ?Product {
        $productStmt = $this->db->getConnection()->prepare(
            'SELECT p.id, p.name, p.in_stock, p.description, p.category_id, c.name AS category_name, p.brand 
             FROM products p
             JOIN categories c ON p.category_id = c.id
             WHERE p.id = :productId'
        );
        $productStmt->execute(['productId' => $productId]);
        $productRow = $productStmt->fetch(PDO::FETCH_ASSOC);
        if (!$productRow) {
            return null;
        }
        $categoryStmt = $this->db->getConnection()->prepare('SELECT id, name FROM categories WHERE id = :id');
        $categoryStmt->execute(['id' => $productRow['category_id']]);
        $catRow = $categoryStmt->fetch(PDO::FETCH_ASSOC);
        $category = new Category($catRow['id'], $catRow['name']);
        $galleryStmt = $this->db->getConnection()->prepare(
            'SELECT image_url FROM product_gallery WHERE product_id = :productId'
        );
        $galleryStmt->execute(['productId' => $productId]);
        $images = $galleryStmt->fetchAll(PDO::FETCH_COLUMN);
        
        $gallery = new ProductGallery($productRow['id'], $images);
        
        $attributeSetStmt = $this->db->getConnection()->prepare(
            'SELECT ats.attribute_type_id, at.name AS type_name, at.type 
             FROM attribute_sets ats
             JOIN attribute_types at ON ats.attribute_type_id = at.id
             WHERE ats.product_id = :productId'
        );
        $attributeSetStmt->execute(['productId' => $productId]);
        $attributeSetsRows = $attributeSetStmt->fetchAll(PDO::FETCH_ASSOC);
        $attributeSets = [];
        foreach ($attributeSetsRows as $row) {
            $attributesStmt = $this->db->getConnection()->prepare(
                'SELECT a.id, a.value, a.display_value 
                 FROM attributes a
                 JOIN attribute_sets ats ON ats.attribute_id = a.id
                 WHERE ats.product_id = :productId AND ats.attribute_type_id = :attributeTypeId'
            );
            $attributesStmt->execute([
                'productId' => $productId,
                'attributeTypeId' => $row['attribute_type_id']
            ]);

            $attributes = array_map(
                fn($attrRow) => new Attribute($attrRow['id'], $attrRow['value'], $attrRow['display_value']),
                $attributesStmt->fetchAll(PDO::FETCH_ASSOC)
            );

            $attributeSets[] = new AttributeSet(
                $productRow['id'],
                $row['attribute_type_id'],
                $row['type'],
                $row['type_name'],
                $attributes,
                $row['attribute_type_id']
            );
        }
        $attributeSets = array_map(
            'unserialize',
            array_unique(
                array_map('serialize', $attributeSets)
            )
        );
        
        $priceStmt = $this->db->getConnection()->prepare(
            'SELECT pr.id, pr.amount, cu.label, cu.symbol, cu.id as cuid 
             FROM prices pr
             JOIN currencies cu ON pr.currency_id = cu.id
             WHERE pr.product_id = :productId'
        );
        $priceStmt->execute(['productId' => $productId]);
        $currencyStmt = $this->db->getConnection()->prepare(
            'SELECT id, label, symbol FROM currencies WHERE id = :currencyId'
        );
        $priceRows = $priceStmt->fetchAll(PDO::FETCH_ASSOC);

        $prices = array_map(function ($priceRow) use ($currencyStmt, $productId) {
            $price = new Price($priceRow['id'], $productId, $priceRow['cuid'], $priceRow['amount']);
            $currencyStmt->execute(['currencyId' => $priceRow['cuid']]);
            $currencyRow = $currencyStmt->fetch(PDO::FETCH_ASSOC);
            if ($currencyRow) {
                $currency = new Currency($currencyRow['id'], $currencyRow['label'], $currencyRow['symbol']);
                $price->setCurrency($currency);
            }
    
            return $price;
        }, $priceRows);
        $product = new Product(
            $productRow['name'],
            (bool) $productRow['in_stock'],
            $productRow['description'],
            (int) $productRow['category_id'],
            $productRow['brand'],
            $gallery,
            $attributeSets,
            $prices[0] ?? null, 
            $productRow['id'],
            $category
        );
        return $product;
    }
    public function getAll(): array {
        $productsStmt = $this->db->getConnection()->prepare(
            'SELECT p.id, p.name, p.in_stock, p.description, p.category_id, c.name AS category_name, p.brand 
             FROM products p
             JOIN categories c ON p.category_id = c.id'
        );
        $productsStmt->execute();
        $productsRows = $productsStmt->fetchAll(PDO::FETCH_ASSOC);
    
        $products = [];
    
        foreach ($productsRows as $productRow) {
            // Category
            $category = new Category($productRow['category_id'], $productRow['category_name']);
    
            // Gallery
            $galleryStmt = $this->db->getConnection()->prepare(
                'SELECT image_url FROM product_gallery WHERE product_id = :productId'
            );
            $galleryStmt->execute(['productId' => $productRow['id']]);
            $images = $galleryStmt->fetchAll(PDO::FETCH_COLUMN);
            $gallery = new ProductGallery($productRow['id'], $images);
    
            // Attribute Sets
            $attributeSetStmt = $this->db->getConnection()->prepare(
                'SELECT ats.attribute_type_id, at.name AS type_name, at.type 
                 FROM attribute_sets ats
                 JOIN attribute_types at ON ats.attribute_type_id = at.id
                 WHERE ats.product_id = :productId'
            );
            $attributeSetStmt->execute(['productId' => $productRow['id']]);
            $attributeSetsRows = $attributeSetStmt->fetchAll(PDO::FETCH_ASSOC);
            $attributeSets = [];
    
            foreach ($attributeSetsRows as $row) {
                $attributesStmt = $this->db->getConnection()->prepare(
                    'SELECT a.id, a.value, a.display_value 
                     FROM attributes a
                     JOIN attribute_sets ats ON ats.attribute_id = a.id
                     WHERE ats.product_id = :productId AND ats.attribute_type_id = :attributeTypeId'
                );
                $attributesStmt->execute([
                    'productId' => $productRow['id'],
                    'attributeTypeId' => $row['attribute_type_id']
                ]);
    
                $attributes = array_map(
                    fn($attrRow) => new Attribute($attrRow['id'], $attrRow['value'], $attrRow['display_value']),
                    $attributesStmt->fetchAll(PDO::FETCH_ASSOC)
                );
    
                $attributeSets[] = new AttributeSet(
                    $productRow['id'],
                    $row['attribute_type_id'],
                    $row['type'],
                    $row['type_name'],
                    $attributes,
                    $row['attribute_type_id']
                );
            }
    
            $attributeSets = array_map(
                'unserialize',
                array_unique(
                    array_map('serialize', $attributeSets)
                )
            );
    
            // Prices
            $priceStmt = $this->db->getConnection()->prepare(
                'SELECT pr.id, pr.amount, cu.label, cu.symbol, cu.id as cuid 
                 FROM prices pr
                 JOIN currencies cu ON pr.currency_id = cu.id
                 WHERE pr.product_id = :productId'
            );
            $priceStmt->execute(['productId' => $productRow['id']]);
            $currencyStmt = $this->db->getConnection()->prepare(
                'SELECT id, label, symbol FROM currencies WHERE id = :currencyId'
            );
            $priceRows = $priceStmt->fetchAll(PDO::FETCH_ASSOC);
    
            $prices = array_map(function ($priceRow) use ($currencyStmt, $productRow) {
                $price = new Price($priceRow['id'], $productRow['id'], $priceRow['cuid'], $priceRow['amount']);
                $currencyStmt->execute(['currencyId' => $priceRow['cuid']]);
                $currencyRow = $currencyStmt->fetch(PDO::FETCH_ASSOC);
                if ($currencyRow) {
                    $currency = new Currency($currencyRow['id'], $currencyRow['label'], $currencyRow['symbol']);
                    $price->setCurrency($currency);
                }
    
                return $price;
            }, $priceRows);
    
            // Create Product
            $products[] = new Product(
                $productRow['name'],
                (bool) $productRow['in_stock'],
                $productRow['description'],
                (int) $productRow['category_id'],
                $productRow['brand'],
                $gallery,
                $attributeSets,
                $prices[0] ?? null,
                $productRow['id'],
                $category
            );
        }
    
        return $products;
    }
    public function updateProduct(string $id, array $data): bool {
        $stmt = $this->db->getConnection()->prepare(
            'UPDATE products 
             SET name = :name, in_stock = :in_stock, description = :description, category_id = :category_id, brand = :brand 
             WHERE id = :id'
        );
        $productStmt = $this->db->getConnection()->prepare(
            'SELECT * FROM `products` WHERE id = :id'
        );
        $productStmt->execute(['id' => $id]);
        $productRow = $productStmt->fetch(PDO::FETCH_ASSOC);
        if($data['name']==null) {
            $data['name'] =$productRow['name'];
        }
        if($data['in_stock']==null) {
            $data['in_stock'] =$productRow['in_stock'];
        }
        if($data['description']==null) {
            $data['description'] =$productRow['description'];
        }
        if($data['category_id']==null) {
            $data['category_id'] =$productRow['category_id'];
        }
        if($data['brand']==null) {
            $data['brand'] =$productRow['brand'];
        }
        return $stmt->execute([
            'id' => $id,
            'name' => $data['name'],
            'in_stock' => $data['in_stock'],
            'description' => $data['description'],
            'category_id' => $data['category_id'],
            'brand' => $data['brand'],
        ]);
    }
    
    public function createProduct(array $data): bool {
        $stmt = $this->db->getConnection()->prepare(
            'INSERT INTO products (id, name, in_stock, description, category_id, brand) 
             VALUES (:id, :name, :in_stock, :description, :category_id, :brand)'
        );
        $priceStmt = $this->db->getConnection()->prepare(
            'INSERT INTO prices (id, product_id, currency_id, amount) 
            VALUES (NULL, :id, 1, :price)'
        );
        $result = $stmt->execute([
            'id' => $data['id'],
            'name' => $data['name'],
            'in_stock' => (bool)$data['in_stock'],
            'description' => $data['description'] ?? null,
            'category_id' => $data['category_id'],
            'brand' => $data['brand'] ?? null,
        ]);
        print_r((double)$data['amount']);
        $priceStmt->execute([
            'id' => $data['id'],
            'price' => (double)$data['amount']
        ]);
        return $result; 

    }
    public function addPictureToGallery(string $productId, string $imgUrl): bool {
        
            $stmt = $this->db->getConnection()->prepare(
                'INSERT INTO product_gallery (product_id, image_url) 
                 VALUES (:product_id, :image_url)'
            );
            $result = $stmt->execute([
                'product_id' => $productId,
                'image_url' => $imgUrl,
            ]);
            
    
            return $result;
        
    }
    
    public function deleteProduct(string $id): bool {
        $stmt = $this->db->getConnection()->prepare(
            'DELETE FROM prices WHERE product_id = :id'
        );
        $stmt->execute(['id' => $id]);
        $stmt = $this->db->getConnection()->prepare(
            'DELETE FROM product_gallery WHERE product_id = :id'
        );
        $stmt->execute(['id' => $id]);
        $stmt = $this->db->getConnection()->prepare(
            'DELETE FROM products WHERE id = :id'
        );
    
        return $stmt->execute(['id' => $id]);
    }
        
}
