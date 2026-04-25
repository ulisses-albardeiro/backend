<?php

namespace App\DTO\Response\Product;

readonly class InventoryMovementOutputDTO
{
    public function __construct(
        public int $id,
        public float $quantity,
        public string $typeLabel,
        public string $typeCode,
        public int $unitPrice,
        public ?string $description,
        public \DateTimeInterface $createdAt,
    ) {}
}
