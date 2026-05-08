<?php

namespace App\DTO\Request\Customer;

use App\Enum\CustomerType;
use Symfony\Component\Validator\Constraints as Assert;

readonly class CustomerAssetInputDTO
{
    public function __construct(
        
        #[Assert\NotBlank]
        #[Assert\Length(max: 255)]
        public string $name,
    ) {}
}
