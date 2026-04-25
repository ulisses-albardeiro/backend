<?php

namespace App\DTO\Response\Product;

readonly class SupplierOutputDTO
{
    public function __construct(
        public int $id,
        public string $name,
        public ?string $document,
        public string $typeLabel,
        public ?string $email,
        public ?string $phone,
        public string $statusLabel,
    ) {}
}
