<?php

namespace App\DTO\Response\Product;

readonly class CategoryOutputDTO
{
    public function __construct(
        public int $id,
        public string $name,
        public ?int $parentId,
        public ?string $parentName,
        public ?string $color,
        public ?string $icon,
        public string $statusLabel,
        public string $statusCode,

        /** @var CategoryOutputDTO[] */
        public array $subCategories = []
    ) {}
}
