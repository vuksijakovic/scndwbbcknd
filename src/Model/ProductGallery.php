<?php

namespace App\Model;

class ProductGallery extends BaseModel {
    private string $productId;
    private array $images;

    public function __construct(string $productId, array $images = [], int $id = 0) {
        parent::__construct($id);
        $this->productId = $productId;
        $this->images = $images;
    }

    public function getProductId(): string {
        return $this->productId;
    }

    public function setProductId(string $productId): void {
        $this->productId = $productId;
    }

    public function getImages(): array {
        return $this->images;
    }

    public function setImages(array $images): void {
        $this->images = $images;
    }

    public function addImage(string $image): void {
        $this->images[] = $image;
    }

    public function validate(): void {
        if (empty($this->productId)) {
            throw new \InvalidArgumentException("Product ID cannot be empty.");
        }

        if (!is_array($this->images) || empty($this->images)) {
            throw new \InvalidArgumentException("Images must be a non-empty array of strings.");
        }

        foreach ($this->images as $image) {
            if (!is_string($image) || empty($image)) {
                throw new \InvalidArgumentException("Each image must be a non-empty string.");
            }
        }
    }

    public function toArray(): array {
        return $this->getImages();
    }
}
