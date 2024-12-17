<?php

namespace App\Model;

class Attribute extends BaseModel {
    private string $value;
    private string $displayValue;

    public function __construct(string $value, string $displayValue, string $id) {
        parent::__construct($id);
        $this->value = $value;
        $this->displayValue = $displayValue;
    }

    public function getValue(): string {
        return $this->value;
    }

    public function setValue(string $value): void {
        $this->value = $value;
    }

    public function getDisplayValue(): string {
        return $this->displayValue;
    }

    public function setDisplayValue(string $displayValue): void {
        $this->displayValue = $displayValue;
    }

    public function validate(): void {
        if (empty($this->value)) {
            throw new \InvalidArgumentException("Value cannot be empty.");
        }

        if (empty($this->displayValue)) {
            throw new \InvalidArgumentException("Display value cannot be empty.");
        }
    }

    public function toArray(): array {
        return [
            'id' => $this->getId(),
            'value' => $this->getValue(),
            'displayValue' => $this->getDisplayValue(),
        ];
    }
}
