<?php

namespace App\DTO\Request;

use App\Enum\TransactionStatus;
use App\Enum\TransactionType;
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
    #[Assert\Choice(callback: [TransactionType::class, 'values'])]
    public string $type;

    #[Assert\NotBlank]
    #[Assert\Choice(callback: [TransactionStatus::class, 'values'])]
    public string $status;

    #[Assert\NotBlank]
    #[Assert\Type('integer')]
    public int $categoryId;

    #[Assert\Type('integer')]
    public ?int $customerId = null;
}
