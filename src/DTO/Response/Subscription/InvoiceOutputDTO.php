<?php

namespace App\DTO\Response\Subscription;

use DateTimeImmutable;

readonly class InvoiceOutputDTO
{
    public function __construct(
        public int $id,
        public string $status,
        public string $statusLabel,
        public string $billingType,
        public string $billingTypeLabel,
        public int $valueCents,
        public DateTimeImmutable $dueDate,
        public ?DateTimeImmutable $paidAt,
        public ?string $invoiceUrl,
    ) {}
}
