<?php

namespace App\DTO\Request;

use App\Enum\UnitType;
use Symfony\Component\Validator\Constraints as Assert;

class PriceListItemInputDTO
{
    #[Assert\NotBlank]
    public string $name;

    #[Assert\NotBlank]
    #[Assert\Type(type: 'numeric')]
    #[Assert\Positive]
    public string $quantity;

    #[Assert\NotBlank]
    public UnitType $unit;
}
