<?php

namespace App\DTO\Request\Product;

use App\Enum\Product\ProductCategoryStatus;
use Symfony\Component\Validator\Constraints as Assert;

readonly class CategoryInputDTO
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Length(max: 255)]
        public string $name,

        public ?int $parentId = null,

        #[Assert\Length(max: 50)]
        public ?string $color = null,

        #[Assert\Length(max: 50)]
        public ?string $icon = null,

        #[Assert\NotBlank]
        public ProductCategoryStatus $status = ProductCategoryStatus::ACTIVE,
    ) {}
}
