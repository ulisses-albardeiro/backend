<?php

namespace App\Mapper\Subscription;

use App\Entity\Subscription\Plan;
use App\DTO\Response\Subscription\PlanOutputDTO;
use App\DTO\Response\Subscription\PlanAdminOutputDTO;
use App\DTO\Request\Subscription\UpdatePlanInputDTO;
use App\Enum\Subscription\PlanBillingCycle;

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

    public function toAdminOutputDTO(Plan $plan): PlanAdminOutputDTO
    {
        return new PlanAdminOutputDTO(
            id: $plan->getId(),
            code: $plan->getCode(),
            name: $plan->getName(),
            priceCents: $plan->getPriceCents(),
            billingCycle: $plan->getBillingCycle()->value,
            billingCycleLabel: $plan->getBillingCycle()->getLabel(),
            trialDays: $plan->getTrialDays(),
            active: $plan->isActive(),
            sortOrder: $plan->getSortOrder(),
            createdAt: $plan->getCreatedAt(),
            updatedAt: $plan->getUpdatedAt(),
        );
    }

    public function toEntity(UpdatePlanInputDTO $dto, Plan $plan): Plan
    {
        $plan->setName($dto->name);
        $plan->setCode($dto->code);
        $plan->setPriceCents($dto->priceCents);
        $plan->setBillingCycle(PlanBillingCycle::from($dto->billingCycle));
        $plan->setTrialDays($dto->trialDays);
        $plan->setActive($dto->active);
        $plan->setSortOrder($dto->sortOrder);

        return $plan;
    }
}
