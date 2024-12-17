<?php

namespace App\Model;

class Category extends BaseModel {
    private string $name;

    public function __construct(int $id = 0, string $name = '') {
        parent::__construct(strval($id));
        $this->name = $name;
    }

    public function getName(): string {
        return $this->name;
    }

    public function setName(string $name): void {
        $this->name = $name;
    }

    public function validate(): void {
        if (empty($this->name)) {
            throw new \InvalidArgumentException('Category name cannot be empty');
        }
    }

    public function toArray(): array {
        return [
            'id' => $this->getId(),
            'name' => $this->getName(),
        ];
    }
}
