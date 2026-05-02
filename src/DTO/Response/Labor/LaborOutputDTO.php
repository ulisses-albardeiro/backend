<?php

namespace App\DTO\Response\Labor;

readonly class LaborOutputDTO
{
    public function __construct(
        public int $id,
        public string $name,
        public LaborCategoryOutputDTO $category,
        public ?string $description,
        
        public int $salePrice,
        
        public string $unitLabel,
        public string $unit,
        
        public string $statusLabel,
        public string $status,
        
        public \DateTimeInterface $createdAt,
        public ?\DateTimeInterface $updatedAt = null,
    ) {}
}
