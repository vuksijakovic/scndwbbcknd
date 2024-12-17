<?php

namespace App\Controller;

use GraphQL\GraphQL as GraphQLBase;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Schema;
use GraphQL\Type\SchemaConfig;
use App\Repository\CategoryRepository;
use App\Repository\PriceRepository;
use App\Repository\ProductRepository;
use App\Repository\AttributeRepository;
use App\Repository\OrderRepository;

use App\Model\Category;
use App\Model\Product;
use App\Model\Attribute;

use Throwable;

class GraphQL {
    static public function handle() {
        try {
            $db = new \App\Service\Database(); // Pretpostavljamo da već imaš klasu za povezivanje s bazom

            $categoryType = new ObjectType([
                'name' => 'Category',
                'fields' => [
                    'id' => Type::int(),
                    'name' => Type::string(),
                    '__typename' => ['type' => Type::string()],
                ],
            ]);
            $currencyType = new ObjectType([
                'name' => 'Currency',
                'fields' => [
                    'label' => Type::string(),
                    'symbol' => Type::string(),
                    '__typename' => Type::string(),
                ],
            ]);
            $priceType = new ObjectType([
                'name' => 'Price',
                'fields' => [
                    'amount' => Type::float(),
                    'currency' => $currencyType,
                    '__typename' => Type::string(),
                ],
            ]);
            $attributeType =new ObjectType([
                'name' => 'Attribute',
                'fields' => [
                    'id' => Type::string(),
                    'displayValue' => Type::string(),
                    'value' => Type::string(),
                    '__typename' => Type::string(),
                ],
            ]);
            $attributeTypeType =new ObjectType([
                'name' => 'AttributeType',
                'fields' => [
                    'id' => Type::string(),
                    'name' => Type::string(),
                    'type' => Type::string(),
                    '__typename' => Type::string(),
                ],
            ]);
            $attributeSetType = new ObjectType([
                'name' => 'AttributeSet',
                'fields' => [
                    'id' => Type::string(),
                    'name' => Type::string(),
                    'type' => Type::string(),
                    'items' => Type::listOf($attributeType),
                    '__typename' => Type::string(),
                ],
            ]);
            $productType = new ObjectType([
                'name' => 'Product',
                'fields' => [
                    'id' => Type::string(),
                    'name' => Type::string(),
                    'inStock' => Type::boolean(),
                    'gallery' => Type::listOf(Type::string()),
                    'description' => Type::string(),
                    'category' => Type::string(),
                    'brand' => Type::string(),
                    'attributes' => Type::listOf($attributeSetType),
                    'prices' => $priceType,
                    '__typename' => Type::string(),
                ],
            ]);
            
            $queryType = new ObjectType([
                'name' => 'Query',
                
                'fields' => [
                    'product' => [
            'type' => $productType,
            'args' => [
                'id' => Type::nonNull(Type::string()),
            ],
            'resolve' => function ($root, $args, $context) use ($db) {
                $repository = new ProductRepository($db);
                $product = $repository->getById($args['id']);
                return $product ? $product->toArray() : null;
            },
        ],
        'products' => [
    'type' => Type::listOf($productType), // Vraća niz proizvoda
    'resolve' => function ($root, $args, $context) use ($db) {
        $repository = new ProductRepository($db);
        $products = $repository->getAll(); // Koristi metodu koja vraća sve proizvode
        return array_map(fn($product) => $product->toArray(), $products);
    },
],
'attributes' => [
    'type' => Type::listOf($attributeType),
    'resolve' => function ($root, $args, $context) use ($db) {
        $repository = new AttributeRepository($db);
        $attributes = $repository->getAllAttributes();
        return array_map(fn($attr) => (new Attribute($attr['id'], $attr['value'], $attr['display_value']))->toArray(), $attributes);
    },
],
'attribute' => [
    'type' => $attributeType, // $attributeType je GraphQL tip za Attribute
    'args' => [
        'id' => Type::nonNull(Type::string()),
    ],
    'resolve' => function ($root, $args, $context) use ($db) {
        $repository = new AttributeRepository($db);
        $attribute = $repository->getAttributeById($args['id']);
        return $attribute ? (new Attribute($attribute['id'], $attribute['value'], $attribute['display_value']))->toArray() : null;
    },
],'attributeTypes' => [
    'type' => Type::listOf($attributeTypeType),
    'resolve' => function ($root, $args, $context) use ($db) {
        $repository = new AttributeRepository($db);
        return $repository->getAllAttributeTypes();
    },
],
'attributeType' => [
    'type' => $attributeTypeType, 
    'args' => [
        'id' => Type::nonNull(Type::string())
    ],
    'resolve' => function ($root, $args, $context) use ($db) {
        $repository = new AttributeRepository($db);
        $attributeType = $repository->getAttributeTypeById($args['id']);
        return $attributeType ? $attributeType : null;
    },
],

                    'categories' => [
                        'type' => Type::listOf($categoryType),
                        'resolve' => function () use ($db) {
                            $repository = new CategoryRepository($db);
                            return array_map(
                                fn($category) => $category->toArray(),
                                $repository->getAll()
                            );
                        },
                    ],
                    'category' => [
                        'type' => $categoryType,
                        'args' => [
                            'id' => ['type' => Type::nonNull(Type::int())],
                        ],
                        'resolve' => function ($rootValue, array $args) use ($db) {
                            $repository = new CategoryRepository($db);
                            $category = $repository->getById($args['id']);
                            return $category ? $category->toArray() : null;
                        },
                    ],
                ],
            ]);

            // Mutation for creating a new category
            $mutationType = new ObjectType([
                'name' => 'Mutation',
                'fields' => [
                    'createAttribute' => [
    'type' => Type::boolean(),
    'args' => [
        'id' => Type::nonNull(Type::string()),
        'value' => Type::nonNull(Type::string()),
        'display_value' => Type::nonNull(Type::string()),
    ],
    'resolve' => function ($root, $args, $context) use ($db) {
        $repository = new AttributeRepository($db);
        return $repository->createAttribute($args);
    },
],
'createAttributeType' => [
    'type' => Type::boolean(),
    'args' => [
        'id' => Type::nonNull(Type::string()),
        'name' => Type::nonNull(Type::string()),
        'type' => Type::nonNull(Type::string())
    ],
    'resolve' => function ($root, $args, $context) use ($db) {
        $repository = new AttributeRepository($db);
        return $repository->createAttributeType($args);
    },
],
'updateAttributeType' => [
    'type' => Type::boolean(),
    'args' => [
        'id' => Type::nonNull(Type::string()),
        'name' => Type::nonNull(Type::string()),
        'type' => Type::nonNull(Type::string())
    ],
    'resolve' => function ($root, $args, $context) use ($db) {
        $repository = new AttributeRepository($db);
        return $repository->updateAttributeType($args['id'], $args);
    },
],
'deleteAttributeType' => [
    'type' => Type::boolean(),
    'args' => [
        'id' => Type::nonNull(Type::string())
    ],
    'resolve' => function ($root, $args, $context) use ($db) {
        $repository = new AttributeRepository($db);
        return $repository->deleteAttributeType($args['id']);
    },
],
'addAttributeSets' => [
    'type' => Type::boolean(),
    'args' => [
        'product_id' => Type::nonNull(Type::string()),
        'attribute_type_id' => Type::nonNull(Type::string()),
        'attribute_ids' => Type::nonNull(Type::listOf(Type::string()))
    ],
    'resolve' => function ($root, $args, $context) use ($db) {
        $repository = new AttributeRepository($db);
        return $repository->addAttributeSets($args['product_id'], $args['attribute_type_id'], $args['attribute_ids']);
    },
],
'deleteAttributeSets' => [
    'type' => Type::boolean(),
    'args' => [
        'product_id' => Type::nonNull(Type::string()),
        'attribute_type_id' => Type::nonNull(Type::string())
    ],
    'resolve' => function ($root, $args, $context) use ($db) {
        $repository = new AttributeRepository($db);
        return $repository->deleteAttributeSets($args['product_id'], $args['attribute_type_id']);
    },
],

'updateAttribute' => [
    'type' => Type::boolean(),
    'args' => [
        'id' => Type::nonNull(Type::string()),
        'value' => Type::nonNull(Type::string()),
        'display_value' => Type::nonNull(Type::string()),
    ],
    'resolve' => function ($root, $args, $context) use ($db) {
        $repository = new AttributeRepository($db);
        return $repository->updateAttribute($args['id'], $args);
    },
],
'addOrder' => [
    'type' => Type::boolean(),
    'args' => [
        'items' => Type::nonNull(Type::string()),
        'total' => Type::nonNull(Type::float()),
    ],
    'resolve' => function ($root, $args, $context) use ($db) {
        $repository = new OrderRepository($db);
        return $repository->addOrder($args['items'], $args['total']);
    },
],

'deleteAttribute' => [
    'type' => Type::boolean(),
    'args' => [
        'id' => Type::nonNull(Type::string()),
    ],
    'resolve' => function ($root, $args, $context) use ($db) {
        $repository = new AttributeRepository($db);
        return $repository->deleteAttribute($args['id']);
    },
],

                    'createProduct' => [
    'type' => Type::boolean(), 
    'args' => [
        'id' => Type::nonNull(Type::string()),
        'name' => Type::nonNull(Type::string()),
        'inStock' => Type::nonNull(Type::boolean()),
        'description' => Type::string(),
        'categoryId' => Type::nonNull(Type::int()),
        'brand' => Type::string(),
        'price' => Type::float()
    ],
    'resolve' => function ($root, $args, $context) use ($db) {
        $repository = new ProductRepository($db);
        return $repository->createProduct([
            'id' => $args['id'],
            'name' => $args['name'],
            'in_stock' => $args['inStock'],
            'description' => $args['description'],
            'category_id' => $args['categoryId'],
            'brand' => $args['brand'],
            'amount' => $args['price']
        ]);
    },
],
'addPictureToGallery' => [
    'type' => Type::boolean(),
    'args' => [
        'product_id' => Type::nonNull(Type::string()),
        'image_url' => Type::nonNull(Type::string()),
    ],
    'resolve' => function ($root, $args, $context) use ($db) {
        $repository = new ProductRepository($db);
        return $repository->addPictureToGallery($args['product_id'], $args['image_url']);
    },
],
'updateProduct' => [
    'type' => Type::boolean(),
    'args' => [
        'id' => Type::nonNull(Type::string()),
        'name' => Type::string(),
        'inStock' => Type::boolean(),
        'description' => Type::string(),
        'categoryId' => Type::int(),
        'brand' => Type::string(),
    ],
    'resolve' => function ($root, $args, $context) use ($db) {
        $repository = new ProductRepository($db);
        return $repository->updateProduct($args['id'], [
            'name' => $args['name'] ?? null,
            'in_stock' => $args['inStock'] ?? null,
            'description' => $args['description'] ?? null,
            'category_id' => $args['categoryId'] ?? null,
            'brand' => $args['brand'] ?? null,
        ]);
    },
],

'deleteProduct' => [
    'type' => Type::boolean(), // Tip vraća samo true/false
    'args' => [
        'id' => Type::nonNull(Type::string()),
    ],
    'resolve' => function ($root, $args, $context) use ($db) {
        $repository = new ProductRepository($db);
        return $repository->deleteProduct($args['id']);
    },
],

                    'createCategory' => [
                        'type' => Type::boolean(),
                        'args' => [
                            'name' => ['type' => Type::nonNull(Type::string())],
                        ],
                        'resolve' => function ($rootValue, array $args) use ($db) {
                            $repository = new CategoryRepository($db);
                            $category = new Category(0, $args['name']);
                            $category->validate();
                            return $repository->create($category);
                        },
                    ],
                    'updateCategory' => [
                        'type' => Type::boolean(),
                        'args' => [
                            'id' => ['type' => Type::nonNull(Type::int())],
                            'name' => ['type' => Type::nonNull(Type::string())],
                        ],
                        'resolve' => function ($rootValue, array $args) use ($db) {
                            $repository = new CategoryRepository($db);
                            $category = new Category($args['id'], $args['name']);
                            return $repository->update($category);
                        },
                    ],
                    'deleteCategory' => [
                        'type' => Type::boolean(),
                        'args' => [
                            'id' => ['type' => Type::nonNull(Type::int())],
                        ],
                        'resolve' => function ($rootValue, array $args) use ($db) {
                            $repository = new CategoryRepository($db);
                            return $repository->delete($args['id']);
                        },
                    ],
                ],
            ]);

            $schema = new Schema(
                (new SchemaConfig())
                    ->setQuery($queryType)
                    ->setMutation($mutationType)
            );

            $rawInput = file_get_contents('php://input');
            $input = json_decode($rawInput, true);
            $query = $input['query'];
            $variableValues = $input['variables'] ?? null;

            $rootValue = [];
            $result = GraphQLBase::executeQuery($schema, $query, $rootValue, null, $variableValues);
            $output = $result->toArray();
        } catch (Throwable $e) {
            $output = [
                'error' => [
                    'message' => $e->getMessage(),
                ],
            ];
        }

        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode($output);
    }
}
