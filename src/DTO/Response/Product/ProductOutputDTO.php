<?php

namespace App\DTO\Response\Product;

readonly class ProductOutputDTO
{
    /**
     * @param string[] $images URLs das imagens do produto
     */
    public function __construct(
        public int $id,
        public string $name,
        public CategoryOutputDTO $category,
        public ?BrandOutputDTO $brand,
        public ?SupplierOutputDTO $supplier,
        public ?string $sku,
        public ?string $barcode,
        public ?string $description,
        
        public int $purchasePrice,
        public int $salePrice,
        
        public string $unitLabel,
        public string $unitCode,
        public float $stockQuantity,
        public float $minStock,
        public ?string $ncm,
        public string $statusLabel,
        public array $images,
        public \DateTimeInterface $createdAt,
    ) {}
}
