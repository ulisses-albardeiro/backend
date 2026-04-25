<?php

namespace App\DTO\Request\Product;

use App\Enum\Product\PersonType;
use App\Enum\Product\ProductSupplierStatus;
use Symfony\Component\Validator\Constraints as Assert;

readonly class SupplierInputDTO
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Length(max: 255)]
        public string $name,

        #[Assert\Length(max: 20)]
        public ?string $document = null,

        #[Assert\NotBlank]
        public PersonType $type = PersonType::JURIDICA,

        #[Assert\Email]
        #[Assert\Length(max: 255)]
        public ?string $email = null,

        #[Assert\Length(max: 20)]
        public ?string $phone = null,

        #[Assert\NotBlank]
        public ProductSupplierStatus $status = ProductSupplierStatus::ACTIVE,
    ) {}
}
