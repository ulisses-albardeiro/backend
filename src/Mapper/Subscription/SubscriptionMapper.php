<?php

namespace App\Mapper\Subscription;

use App\Entity\Subscription\Subscription;
use App\Mapper\Subscription\PlanMapper;
use App\DTO\Response\Subscription\SubscriptionOutputDTO;

class SubscriptionMapper
{
    public function __construct(
        private PlanMapper $planMapper,
    ) {}

    public function toOutputDTO(Subscription $subscription): SubscriptionOutputDTO
    {
        $plan = $subscription->getPlan();

        return new SubscriptionOutputDTO(
            id: $subscription->getId(),
            status: $subscription->getStatus()->value,
            statusLabel: $subscription->getStatus()->getLabel(),
            blocked: $subscription->isBlocked(),
            canChangePlan: $subscription->canChangePlan(),
            billingType: $subscription->getBillingType()->value,
            billingTypeLabel: $subscription->getBillingType()->getLabel(),
            plan: $plan ? $this->planMapper->toOutputDTO($plan) : null,
            trialEndsAt: $subscription->getTrialEndsAt(),
            currentPeriodEnd: $subscription->getCurrentPeriodEnd(),
            cardLastFour: $subscription->getCardLastFour(),
            cardBrand: $subscription->getCardBrand(),
        );
    }
}
