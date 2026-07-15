<?php

namespace App\DTO\Response\Subscription;

readonly class PlanOutputDTO
{
    public function __construct(
        public int $id,
        public string $code,
        public string $name,
        public int $priceCents,
        public string $billingCycle,
        public string $billingCycleLabel,
        public int $trialDays,
    ) {}
}
