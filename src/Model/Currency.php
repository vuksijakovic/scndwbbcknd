<?php

namespace App\Model;
class Currency extends BaseModel{
    private string $label;
    private string $symbol;

    
    public function __construct($id, string $label, string $symbol) {
        parent::__construct(strval($id));
        $this->label = $label;
        $this->symbol = $symbol;
    }

  
    public function getLabel(): string {
        return $this->label;
    }

    public function getSymbol(): string {
        return $this->symbol;
    }
    public function validate(): void {
        if (empty($this->label)) {
            throw new \InvalidArgumentException('Currency label cannot be empty');
        }
    }

    public function toArray(): array {
        return [
            'id' => $this->getId(),
            'label' => $this->getLabel(),
            'symbol' => $this->getSymbol()
        ];
    }
}
