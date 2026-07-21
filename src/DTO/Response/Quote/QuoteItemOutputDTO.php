<?php

namespace App\DTO\Response\Quote;

readonly class QuoteItemOutputDTO
{
    public function __construct(
        public int $id,
        public string $description,
        public string $quantity,
        public int $unitPrice,
        public int $totalPrice,
        public ?int $laborId,
        public ?string $laborName,
        public ?string $laborUnit,
        public ?int $productId,
        public ?string $productName,
        public ?string $productUnit,
        public array $images = [],
    ) {}
}
