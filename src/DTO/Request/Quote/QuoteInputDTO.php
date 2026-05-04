<?php

namespace App\DTO\Request\Quote;

use App\Enum\QuoteStatus;
use DateTimeImmutable;
use Symfony\Component\Validator\Constraints as Assert;

class QuoteInputDTO
{
    #[Assert\NotBlank(message: "O campo Cliente é obrigatório")]
    public int $customerId;

    #[Assert\NotBlank]
    public DateTimeImmutable $date;

    #[Assert\NotBlank]
    public DateTimeImmutable $dueDate;

    public string $discountType = 'none';
    public int $discountValue = 0;
    public int $shippingValue = 0;
    public ?string $description = null;
    public ?string $internalNotes = null;
    public ?string $notes = null;

    #[Assert\NotBlank(message: "O campo Status é obrigatório")]
    public QuoteStatus $status;

    /** @var QuoteItemInputDTO[] */
    #[Assert\Valid]
    #[Assert\Count(min: 1)]
    public array $items = [];
}
