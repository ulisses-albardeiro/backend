<?php

namespace App\DTO\Request\Product;

use App\Enum\Product\InventoryMovementType;
use Symfony\Component\Validator\Constraints as Assert;

readonly class InventoryAdjustmentDTO
{
    public function __construct(
        #[Assert\NotBlank]
        public int $productId,

        #[Assert\NotBlank]
        #[Assert\GreaterThan(0)]
        public float $quantity,

        #[Assert\NotBlank]
        public InventoryMovementType $type,

        #[Assert\Length(max: 255)]
        public ?string $description = null,
    ) {}
}
