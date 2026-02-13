<?php

namespace App\DTO\Response;

readonly class QuoteItemOutputDTO
{
    public function __construct(
        public int $id,
        public string $description,
        public string $quantity,
        public int $unitPrice,
        public int $totalPrice,
    ) {}
}
