<?php

namespace App\Service\Subscription;

use App\Entity\Subscription\Plan;
use App\Mapper\Subscription\PlanMapper;
use App\DTO\Response\Subscription\PlanOutputDTO;
use App\DTO\Response\Subscription\PlanAdminOutputDTO;
use App\DTO\Request\Subscription\UpdatePlanInputDTO;
use App\Repository\Subscription\PlanRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class PlanService
{
    public function __construct(
        private PlanRepository $repository,
        private PlanMapper $mapper,
        private EntityManagerInterface $em,
    ) {}

    /**
     * @return PlanOutputDTO[]
     */
    public function listActive(): array
    {
        $plans = $this->repository->findAllActive();

        return array_map(fn(Plan $plan) => $this->mapper->toOutputDTO($plan), $plans);
    }

    /**
     * @return PlanAdminOutputDTO[]
     */
    public function listAll(): array
    {
        $plans = $this->repository->findBy([], ['sortOrder' => 'ASC']);

        return array_map(fn(Plan $plan) => $this->mapper->toAdminOutputDTO($plan), $plans);
    }

    public function update(int $id, UpdatePlanInputDTO $dto): PlanAdminOutputDTO
    {
        $plan = $this->repository->find($id);

        if (!$plan) {
            throw new NotFoundHttpException('PLAN_NOT_FOUND');
        }

        $existingWithCode = $this->repository->findOneByCode($dto->code);
        if ($existingWithCode && $existingWithCode->getId() !== $plan->getId()) {
            throw new BadRequestHttpException('PLAN_CODE_ALREADY_IN_USE');
        }

        $plan = $this->mapper->toEntity($dto, $plan);

        $this->em->flush();

        return $this->mapper->toAdminOutputDTO($plan);
    }

    public function getDefaultActive(): ?Plan
    {
        return $this->repository->findDefaultActive();
    }
}
