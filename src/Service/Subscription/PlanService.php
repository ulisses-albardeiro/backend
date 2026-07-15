<?php

namespace App\Service\Subscription;

use App\Entity\Subscription\Plan;
use App\Mapper\Subscription\PlanMapper;
use App\DTO\Response\Subscription\PlanOutputDTO;
use App\Repository\Subscription\PlanRepository;

class PlanService
{
    public function __construct(
        private PlanRepository $repository,
        private PlanMapper $mapper,
    ) {}

    /**
     * @return PlanOutputDTO[]
     */
    public function listActive(): array
    {
        $plans = $this->repository->findAllActive();

        return array_map(fn(Plan $plan) => $this->mapper->toOutputDTO($plan), $plans);
    }

    public function getDefaultActive(): ?Plan
    {
        return $this->repository->findDefaultActive();
    }
}
