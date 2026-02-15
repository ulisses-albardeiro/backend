<?php

namespace App\DTO\Request;

use Symfony\Component\Validator\Constraints as Assert;

class QuoteInputDTO
{
    #[Assert\NotBlank]
    public int $customer_id;

    #[Assert\NotBlank]
    public string $date;

    #[Assert\NotBlank]
    public string $due_date;

    public string $discount_type = 'none';
    public int $discount_value = 0;
    public int $shipping_value = 0;
    public ?string $description = null;
    public ?string $notes = null;

    /** @var QuoteItemInputDTO[] */
    #[Assert\Valid]
    #[Assert\Count(min: 1)]
    public array $items = [];
}
