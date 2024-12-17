<?php

namespace App\Model;

class Price extends BaseModel{
    private string $productId;
    private int $currencyId;
    private float $amount;

    private Currency $currency; 

    // Constructor
    public function __construct(int $id, string $productId, int $currencyId, float $amount) {
        parent::__construct(strval($id));
        $this->productId = $productId;
        $this->currencyId = $currencyId;
        $this->amount = $amount;
    }

    // Getters
    

    public function getProductId(): string {
        return $this->productId;
    }

    public function getCurrencyId(): int {
        return $this->currencyId;
    }

    public function getAmount(): float {
        return $this->amount;
    }

    public function getCurrencyLabel(): string {
        return $this->currency->getLabel();
    }

    public function getCurrencySymbol(): string {
        return $this->currency->getSymbol(); 
    }
    public function getCurrency(): Currency {
        return $this->currency;
    }
    public function setCurrency(Currency $currency): void {
        $this->currency = $currency;
    }
    public function validate(): void {
        if (empty($this->amount)) {
            throw new \InvalidArgumentException('Price amount cannot be empty');
        }
    }

    public function toArray(): array {
        return [
            'id' => $this->getId(),
            'amount' => $this->getAmount(),
            'currency' => $this->getCurrency()->toArray()
        ];
    }
}
