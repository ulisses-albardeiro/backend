<?php

namespace App\DTO\Response\Subscription;

use DateTimeImmutable;

readonly class SubscriptionOutputDTO
{
    public function __construct(
        public int $id,
        public string $status,
        public string $statusLabel,
        public bool $blocked,
        public bool $canChangePlan,
        public string $billingType,
        public string $billingTypeLabel,
        public ?PlanOutputDTO $plan,
        public ?DateTimeImmutable $trialEndsAt,
        public ?DateTimeImmutable $currentPeriodEnd,
        public ?string $cardLastFour,
        public ?string $cardBrand,
    ) {}
}
