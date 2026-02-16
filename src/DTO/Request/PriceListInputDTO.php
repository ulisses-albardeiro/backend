<?php

namespace App\DTO\Request;

use Symfony\Component\Validator\Constraints as Assert;

class PriceListInputDTO
{
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    public string $title;

    public ?string $description = null;

    /** * @var PriceListItemInputDTO[] */
    #[Assert\Valid]
    #[Assert\Count(min: 1)]
    public array $items = [];
}
