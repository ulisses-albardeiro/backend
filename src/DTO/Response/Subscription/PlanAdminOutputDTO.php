<?php

namespace App\DTO\Response\Subscription;

readonly class PlanAdminOutputDTO
{
    public function __construct(
        public int $id,
        public string $code,
        public string $name,
        public int $priceCents,
        public string $billingCycle,
        public string $billingCycleLabel,
        public int $trialDays,
        public bool $active,
        public int $sortOrder,
        public \DateTimeImmutable $createdAt,
        public ?\DateTimeImmutable $updatedAt,
    ) {}
}
