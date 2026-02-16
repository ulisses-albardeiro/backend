<?php

namespace App\DTO\Request;

use App\Enum\PaymentMethod;
use Symfony\Component\Validator\Constraints as Assert;

readonly class ReceiptInputDTO
{
    public function __construct(
        
        #[Assert\NotBlank]
        #[Assert\Type('integer')]
        #[Assert\PositiveOrZero]
        public int $amount,

        #[Assert\NotBlank]
        public \DateTimeImmutable $paymentDate,

        #[Assert\NotBlank]
        public PaymentMethod $paymentMethod,

        #[Assert\NotBlank]
        #[Assert\Length(max: 255)]
        public string $description,

        #[Assert\NotBlank]
        #[Assert\Type('integer')]
        public int $customerId,

        public ?int $quoteId = null,

        public ?string $notes = null,
    ) {}
}
