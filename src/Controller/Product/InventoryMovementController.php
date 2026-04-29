<?php

namespace App\Controller\Product;

use App\DTO\Request\Product\InventoryMovementInputDTO;
use App\Entity\User;
use App\Service\Product\InventoryMovementService;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('IS_AUTHENTICATED_FULLY')]
#[Route('/api/movements', name: 'api_movements_', format: 'json')]
final class InventoryMovementController extends AbstractController
{
    public function __construct(
        private LoggerInterface $logger,
        private InventoryMovementService $service,
    ) {}

    #[Route('', name: 'index', methods: ['GET'])]
    public function index(): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();
        
        return $this->json($this->service->listAll($user->getCompany()));
    }

    #[Route('', name: 'store', methods: ['POST'])]
    public function store(
        #[MapRequestPayload] InventoryMovementInputDTO $dto,
    ): JsonResponse {
        try {
            /** @var User $user */
            $user = $this->getUser();

            $output = $this->service->registerMovement($dto, $user->getCompany());

            return $this->json($output, 201);
        } catch (\Exception $e) {
            $this->logger->error('Failed to register inventory movement.', [
                'user_id' => $user->getId(),
                'error' => $e->getMessage()
            ]);

            return $this->json([
                'message' => $e->getMessage() ?: 'ERROR_SAVING_MOVEMENT'
            ], 400);
        }
    }
}
