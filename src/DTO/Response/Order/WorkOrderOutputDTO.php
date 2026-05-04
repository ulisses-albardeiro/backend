<?php

namespace App\DTO\Response\Order;

use DateTimeImmutable;

readonly class WorkOrderOutputDTO
{
    /**
     * @param WorkOrderItemOutputDTO[] $items
     */
    public function __construct(
        public int $id,
        public string $code,
        public string $title,
        public int $companyId,
        public int $customerId,
        public string $customerName,
        public ?int $quoteId,
        public string $status,
        public string $statusLabel,
        public string $statusColor,
        public string $problemDescription,
        public ?string $technicalReport,
        public ?string $equipment,
        public ?DateTimeImmutable $startDate,
        public ?DateTimeImmutable $endDate,
        public DateTimeImmutable $createdAt,
        public ?DateTimeImmutable $updatedAt,
        public int $totalAmount,
        public array $items,
    ) {}
}
