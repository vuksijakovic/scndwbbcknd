<?php

namespace App\Model;

class AttributeSet extends BaseModel {
    private string $productId;
    private string $attributeTypeId;
    private string $typeName;
    private string $name;
    /** @var Attribute[] */
    private array $attributes;

    public function __construct(
        string $productId,
        string $attributeTypeId,
        string $typeName,
        string $name,
        array $attributes = [],
        string $id
    ) {
        parent::__construct($id);
        $this->productId = $productId;
        $this->attributeTypeId = $attributeTypeId;
        $this->typeName = $typeName;
        $this->name = $name;
        $this->attributes = $attributes;
    }

    public function getProductId(): string {
        return $this->productId;
    }

    public function setProductId(int $productId): void {
        $this->productId = $productId;
    }

    public function getAttributeTypeId(): string {
        return $this->attributeTypeId;
    }

    public function setAttributeTypeId(string $attributeTypeId): void {
        $this->attributeTypeId = $attributeTypeId;
    }

    public function getTypeName(): string {
        return $this->typeName;
    }

    public function setTypeName(string $typeName): void {
        $this->typeName = $typeName;
    }

    public function getName(): string {
        return $this->name;
    }

    public function setName(string $name): void {
        $this->name = $name;
    }

    /**
     * @return Attribute[]
     */
    public function getAttributes(): array {
        return $this->attributes;
    }

    /**
     * @param Attribute[] $attributes
     */
    public function setAttributes(array $attributes): void {
        foreach ($attributes as $attribute) {
            if (!$attribute instanceof Attribute) {
                throw new \InvalidArgumentException("Each item in attributes must be an instance of Attribute.");
            }
        }
        $this->attributes = $attributes;
    }

    public function addAttribute(Attribute $attribute): void {
        $this->attributes[] = $attribute;
    }

    public function validate(): void {
        if (empty($this->productId)) {
            throw new \InvalidArgumentException("Product ID cannot be empty.");
        }

        if (empty($this->attributeTypeId)) {
            throw new \InvalidArgumentException("Attribute Type ID cannot be empty.");
        }

        if (empty($this->typeName)) {
            throw new \InvalidArgumentException("Type Name cannot be empty.");
        }

        if (empty($this->name)) {
            throw new \InvalidArgumentException("Name cannot be empty.");
        }

        foreach ($this->attributes as $attribute) {
            $attribute->validate();
        }
    }

    public function toArray(): array {
        return [
            'id' => $this->getAttributeTypeId(),
            'type' => $this->getTypeName(),
            'name' => $this->getName(),
            'items' => array_map(fn($attribute) => $attribute->toArray(), $this->getAttributes()),
        ];
    }
}
