<?php

namespace App\DTO\Request\Product;

use App\Enum\Product\ProductStatus;
use App\Enum\Product\ProductUnit;
use Symfony\Component\Validator\Constraints as Assert;

readonly class ProductInputDTO
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Length(max: 255)]
        public string $name,

        #[Assert\NotBlank]
        public int $categoryId,

        public ?int $brandId = null,
        public ?int $supplierId = null,

        #[Assert\Length(max: 100)]
        public ?string $sku = null,

        #[Assert\Length(max: 20)]
        public ?string $barcode = null,

        public ?string $description = null,

        #[Assert\NotBlank]
        #[Assert\PositiveOrZero]
        public int $purchasePrice = 0, 

        #[Assert\NotBlank]
        #[Assert\PositiveOrZero]
        public int $salePrice = 0,

        #[Assert\NotBlank]
        public ProductUnit $unit = ProductUnit::UNIDADE,

        #[Assert\PositiveOrZero]
        public float $initialStock = 0,

        #[Assert\PositiveOrZero]
        public float $minStock = 0,

        #[Assert\Length(max: 8)]
        public ?string $ncm = null,

        #[Assert\NotBlank]
        public ProductStatus $status = ProductStatus::ACTIVE,
        
        /** @var string[]|null URLs ou Base64 das imagens */
        public ?array $images = null,
    ) {}
}
