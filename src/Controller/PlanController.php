<?php

namespace App\Controller;

use App\Service\Subscription\PlanService;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/api', name: 'api_', format: 'json')]
final class PlanController extends AbstractController
{
    public function __construct(
        private PlanService $planService,
    ) {}

    #[Route('/plans', name: 'plans_list', methods: ['GET'])]
    public function index(): JsonResponse
    {
        return $this->json(['plans' => $this->planService->listActive()]);
    }
}
