<?php

namespace App\Model;

class Product extends BaseModel {
    private string $name;
    private bool $inStock;
    private ?string $description;
    private int $categoryId;
    private string $brand;
    private ProductGallery $gallery;
    /** @var AttributeSet[] */
    private array $attributeSets;
    private ?Price $price;
    private Category $category;
    public function __construct(
        string $name,
        bool $inStock,
        ?string $description,
        int $categoryId,
        string $brand,
        ProductGallery $gallery,
        array $attributeSets = [],
        ?Price $price = null,
        string $id,
        Category $category
    ) {
        parent::__construct($id);
        $this->name = $name;
        $this->inStock = $inStock;
        $this->description = $description;
        $this->categoryId = $categoryId;
        $this->brand = $brand;
        $this->gallery = $gallery;
        $this->attributeSets = $attributeSets;
        $this->price = $price;
        $this->category = $category;
    }

    public function getName(): string {
        return $this->name;
    }

    public function setName(string $name): void {
        $this->name = $name;
    }

    public function isInStock(): bool {
        return $this->inStock;
    }

    public function setInStock(bool $inStock): void {
        $this->inStock = $inStock;
    }

    public function getDescription(): ?string {
        return $this->description;
    }

    public function setDescription(?string $description): void {
        $this->description = $description;
    }

    public function getCategoryId(): int {
        return $this->categoryId;
    }

    public function setCategoryId(int $categoryId): void {
        $this->categoryId = $categoryId;
    }

    public function getBrand(): string {
        return $this->brand;
    }

    public function setBrand(string $brand): void {
        $this->brand = $brand;
    }

    public function getGallery(): ProductGallery {
        return $this->gallery;
    }

    public function setGallery(ProductGallery $gallery): void {
        $this->gallery = $gallery;
    }

    /**
     * @return AttributeSet[]
     */
    public function getAttributeSets(): array {
        return $this->attributeSets;
    }

    /**
     * @param AttributeSet[] $attributeSets
     */
    public function setAttributeSets(array $attributeSets): void {
        foreach ($attributeSets as $attributeSet) {
            if (!$attributeSet instanceof AttributeSet) {
                throw new \InvalidArgumentException("Each item in attributeSets must be an instance of AttributeSet.");
            }
        }
        $this->attributeSets = $attributeSets;
    }

    public function addAttributeSet(AttributeSet $attributeSet): void {
        $this->attributeSets[] = $attributeSet;
    }

    public function getPrice(): ?Price {
        return $this->price;
    }

    public function setPrice(?Price $price): void {
        $this->price = $price;
    }
    public function getCategory(): Category {
        return $this->category;
    }
    public function validate(): void {
        if (empty($this->name)) {
            throw new \InvalidArgumentException("Name cannot be empty.");
        }
        if (empty($this->categoryId)) {
            throw new \InvalidArgumentException("Category ID cannot be empty.");
        }

        $this->gallery->validate();
        foreach ($this->attributeSets as $attributeSet) {
            $attributeSet->validate();
        }

        if ($this->price) {
            $this->price->validate();
        }
    }

    public function toArray(): array {
        return [
            'brand' => $this->getBrand(),
            'name' => $this->getName(),
            'category' => $this->getCategory()->getName(),
            'inStock' => $this->isInStock(),
            'description' => $this->getDescription(),
            'id' => $this->getId(),
            'gallery' => $this->getGallery()->toArray(),
            'prices' => $this->getPrice()->toArray(),
            'attributes' => array_map(fn($attributeSet) => $attributeSet->toArray(), $this->getAttributeSets())
        ];
    }
}
