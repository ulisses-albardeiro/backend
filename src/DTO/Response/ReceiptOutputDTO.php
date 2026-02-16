<?php

namespace App\DTO\Response;

use App\Enum\PaymentMethod;
use App\Enum\ReceiptStatus;

readonly class ReceiptOutputDTO
{
    public function __construct(
        public int $id,
        public string $code,
        public string $amount,
        public \DateTimeImmutable $paymentDate,
        public PaymentMethod $paymentMethod,
        public string $paymentMethodLabel,
        public string $description,
        public ReceiptStatus $status,
        public string $statusLabel,
        public ?int $quoteId,
        public int $customerId,
        public string $customerName,
        public ?string $notes,
        public \DateTimeImmutable $createdAt,
        public ?\DateTimeImmutable $updatedAt,
    ) {}
}
