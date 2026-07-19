<?php

namespace App\Controller\Admin;

use Psr\Log\LoggerInterface;
use App\Service\Subscription\PlanService;
use App\DTO\Request\Subscription\UpdatePlanInputDTO;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

#[Route('api/admin', name: 'admin_')]
#[IsGranted('IS_AUTHENTICATED_FULLY')]
final class PlanAdminController extends AbstractController
{
    public function __construct(
        private LoggerInterface $logger,
        private PlanService $planService,
    ) {}

    #[Route('/plans', name: 'plans_index', methods: ['GET'])]
    #[IsGranted('ROLE_SUPER_ADMIN')]
    public function index(): JsonResponse
    {
        return $this->json($this->planService->listAll());
    }

    #[Route('/plan/{id}', name: 'plan_update', methods: ['PUT'])]
    #[IsGranted('ROLE_SUPER_ADMIN')]
    public function update(int $id, #[MapRequestPayload] UpdatePlanInputDTO $dto): JsonResponse
    {
        try {
            $plan = $this->planService->update($id, $dto);
            return $this->json($plan);
        } catch (NotFoundHttpException $e) {
            return $this->json(['message' => $e->getMessage()], 404);
        } catch (BadRequestHttpException $e) {
            return $this->json(['message' => $e->getMessage()], 400);
        } catch (\Exception $e) {
            $this->logger->error('Update plan error', [
                'plan_id' => $id,
                'error' => $e->getMessage(),
            ]);
            return $this->json(['message' => 'ERROR_UPDATING_PLAN'], 500);
        }
    }
}
