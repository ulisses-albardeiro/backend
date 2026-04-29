<?php

namespace App\Controller\Product;

use App\DTO\Request\Product\SupplierInputDTO;
use App\Service\Product\ProductSupplierService;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('IS_AUTHENTICATED_FULLY')]
#[Route('/api/supplier', name: 'api_supplier_', format: 'json')]
final class SupplierController extends AbstractController
{
    public function __construct(
        private LoggerInterface $logger,
        private ProductSupplierService $service,
    ) {}

    #[Route('', name: 'index', methods: ['GET'])]
    public function index(): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();
        return $this->json($this->service->listAll($user->getCompany()));
    }

    #[Route('/active', name: 'active', methods: ['GET'])]
    public function indexActive(): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();
        return $this->json($this->service->listByStatus($user->getCompany(), 'active'));
    }

    #[Route('', name: 'store', methods: ['POST'])]
    public function store(#[MapRequestPayload] SupplierInputDTO $dto): JsonResponse
    {
        try {
            /** @var User $user */
            $user = $this->getUser();
            return $this->json($this->service->create($dto, $user->getCompany()), 201);
        } catch (\Exception $e) {
            $this->logger->error('Failed to save supplier.', [
                'user_id' => $user->getId(),
                'error' => $e->getMessage()
            ]);
            return $this->json(['message' => 'ERROR_SAVING_SUPPLIER'], 500);
        }
    }

    #[Route('/{id}', name: 'update', methods: ['PUT'])]
    public function update(int $id, #[MapRequestPayload] SupplierInputDTO $dto): JsonResponse
    {
        try {
            /** @var User $user */
            $user = $this->getUser();
            return $this->json($this->service->update($id, $dto, $user->getCompany()));
        } catch (\Exception $e) {
            $this->logger->error('Failed to update supplier.', [
                'supplier_id' => $id,
                'error' => $e->getMessage()
            ]);
            return $this->json(['message' => $e->getMessage()], 400);
        }
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        try {
            /** @var User $user */
            $user = $this->getUser();
            $this->service->delete($id, $user->getCompany());
            return $this->json(null, 204);
        } catch (\Exception $e) {
            $this->logger->error('Failed to delete supplier.', [
                'supplier_id' => $id,
                'error' => $e->getMessage()
            ]);
            return $this->json(['message' => $e->getMessage()], 400);
        }
    }
}
