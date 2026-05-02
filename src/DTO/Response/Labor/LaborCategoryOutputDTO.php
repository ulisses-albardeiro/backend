<?php

namespace App\DTO\Response\Labor;

readonly class LaborCategoryOutputDTO
{
    public function __construct(
        public int $id,
        public string $name,
        public ?int $parentId,
        public ?string $parentName,
        public ?string $color,
        public ?string $icon,
        public string $statusLabel,
        public string $status,

        /** @var LaborCategoryOutputDTO[] */
        public array $subCategories = []
    ) {}
}
