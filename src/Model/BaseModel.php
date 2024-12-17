<?php

namespace App\Model;

abstract class BaseModel {
    protected $id;

    public function __construct($id) {
        $this->id = $id;
    }

    public function getId(): string {
        return $this->id;
    }

    public function setId(int $id): void {
        $this->id = $id;
    }

    abstract public function validate(): void;

    abstract public function toArray(): array;
}
