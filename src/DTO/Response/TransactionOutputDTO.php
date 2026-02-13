<?php

namespace App\DTO\Response;

readonly class TransactionOutputDTO
{
    public function __construct(
        public int $id,
        public string $description,
        public int $amount,
        public float $amountFormatted,
        public string $date,
        public string $type,
        public string $typeLabel,
        public string $status,
        public string $statusLabel,
        public int $categoryId,
        public string $categoryName,
        public string $categoryColor,
        public ?int $customerId,
        public ?string $customerName,
        public string $createdAt,
    ) {}
}
