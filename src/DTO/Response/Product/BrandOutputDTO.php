<?php

namespace App\DTO\Response\Product;

readonly class BrandOutputDTO
{
    public function __construct(
        public int $id,
        public string $name,
        public ?string $logo,
        public string $statusLabel,
        public string $statusCode,
    ) {}
}
