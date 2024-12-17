<?php

namespace App\Model;

class AttributeType extends BaseModel {
    private string $name;
    private string $type;

    public function __construct(string $name, string $type, string $id) {
        parent::__construct($id);
        $this->name = $name;
        $this->type = $type;
    }

    public function getName(): string {
        return $this->name;
    }

    public function setName(string $name): void {
        $this->name = $name;
    }

    public function getType(): string {
        return $this->type;
    }

    public function setType(string $type): void {
        $this->type = $type;
    }

    public function validate(): void {
        if (empty($this->name)) {
            throw new \InvalidArgumentException("Name cannot be empty.");
        }

        if (empty($this->type)) {
            throw new \InvalidArgumentException("Type cannot be empty.");
        }
    }

    public function toArray(): array {
        return [
            'id' => $this->getId(),
            'name' => $this->getName(),
            'type' => $this->getType(),
        ];
    }
}
