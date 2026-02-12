<?php

namespace App\DTO\Response;

readonly class CategoryOutputDTO
{
    public function __construct(
        public int $id,
        public string $name,
        public string $type,
        public string $typeLabel,
        public ?int $parentId,
        public ?string $parentName,
        public ?string $color,
        public ?string $icon,
        public bool $status,
        /** @var CategoryOutputDTO[] */
        public array $subCategories = []
    ) {}
}
