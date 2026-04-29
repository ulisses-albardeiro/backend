<?php

namespace App\DTO\Response\Product;

readonly class InventoryMovementOutputDTO
{
    public function __construct(
        public int $id,
        public int $productId,
        public string $productName,
        public string $typeLabel,
        public string $typeValue,
        public float $quantity,
        public int $unitPrice,
        public ?string $description,
        public \DateTimeInterface $createdAt,
    ) {}
}
