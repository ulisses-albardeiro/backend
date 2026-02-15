<?php
namespace App\DTO\Request;

use Symfony\Component\Validator\Constraints as Assert;


class QuoteItemInputDTO
{
    #[Assert\NotBlank]
    public string $description;

    #[Assert\NotBlank]
    #[Assert\Positive]
    public string $quantity;

    #[Assert\NotBlank]
    #[Assert\PositiveOrZero]
    public int $unit_price;
}
