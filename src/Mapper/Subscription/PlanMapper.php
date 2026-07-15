<?php

namespace App\Mapper\Subscription;

use App\Entity\Subscription\Plan;
use App\DTO\Response\Subscription\PlanOutputDTO;

class PlanMapper
{
    public function toOutputDTO(Plan $plan): PlanOutputDTO
    {
        return new PlanOutputDTO(
            id: $plan->getId(),
            code: $plan->getCode(),
            name: $plan->getName(),
            priceCents: $plan->getPriceCents(),
            billingCycle: $plan->getBillingCycle()->value,
            billingCycleLabel: $plan->getBillingCycle()->getLabel(),
            trialDays: $plan->getTrialDays(),
        );
    }
}
