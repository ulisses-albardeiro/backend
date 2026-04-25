<?php

namespace App\DTO\Request\Product;

use App\Enum\Product\ProductBrandStatus;
use Symfony\Component\Validator\Constraints as Assert;

readonly class BrandInputDTO
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Length(max: 255)]
        public string $name,

        public ?string $logo = null,

        #[Assert\NotBlank]
        public ProductBrandStatus $status = ProductBrandStatus::ACTIVE,
    ) {}
}
