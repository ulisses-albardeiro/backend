<?php

namespace App\DTO\Request\Product;

use App\Enum\Product\InventoryMovementType;
use Symfony\Component\Validator\Constraints as Assert;

readonly class InventoryMovementInputDTO
{
    public function __construct(
        #[Assert\NotBlank]
        public int $productId,

        #[Assert\NotBlank]
        public InventoryMovementType $type,

        #[Assert\NotBlank]
        #[Assert\Positive]
        public float $quantity,

        #[Assert\NotBlank]
        #[Assert\PositiveOrZero]
        public int $unitPrice,

        public ?string $description = null,
    ) {}
}
