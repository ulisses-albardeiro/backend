<?php
namespace App\DTO\Request\Quote;

use Symfony\Component\Validator\Constraints as Assert;

class QuoteItemInputDTO
{
    public ?int $id = null;

    #[Assert\NotBlank]
    public string $description;

    #[Assert\NotBlank]
    #[Assert\Positive]
    public string $quantity;

    #[Assert\NotBlank]
    #[Assert\PositiveOrZero]
    public int $unitPrice;

    #[Assert\Positive]
    public ?int $laborId = null;

    #[Assert\Positive]
    public ?int $productId = null;
}
