<?php

namespace App\DTO\Request;

use Symfony\Component\Validator\Constraints as Assert;

class TransactionInputDTO
{
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    public string $description;

    #[Assert\NotBlank]
    #[Assert\Type('integer')]
    #[Assert\PositiveOrZero]
    public int $amount;

    #[Assert\NotBlank]
    #[Assert\Date]
    public string $date;

    #[Assert\NotBlank]
    public string $type;

    #[Assert\NotBlank]
    public string $status;

    #[Assert\NotBlank]
    #[Assert\Type('integer')]
    public int $categoryId;

    #[Assert\Type('integer')]
    public ?int $customerId = null;
}
