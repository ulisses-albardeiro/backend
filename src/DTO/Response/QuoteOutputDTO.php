<?php

namespace App\DTO\Response;

readonly class QuoteOutputDTO
{
    /**
     * @param QuoteItemOutputDTO[] $items
     */
    public function __construct(
        public int $id,
        public string $code,
        public int $customerId,
        public string $customerName,
        public string $status,
        public string $statusLabel,
        public string $statusColor,
        public string $date,
        public string $dueDate,
        public int $subtotal,
        public string $discountType,
        public ?int $discountValue,
        public ?int $shippingValue,
        public int $totalAmount,
        public ?string $description,
        public ?string $notes,
        public array $items,
    ) {}
}
