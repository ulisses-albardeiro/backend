<?php

namespace App\Controller\Product;

use App\DTO\Request\Product\BrandInputDTO;
use App\Service\Product\BrandService;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('IS_AUTHENTICATED_FULLY')]
#[Route('/api/brand', name: 'api_brand_', format: 'json')]
final class BrandController extends AbstractController
{
    public function __construct(
        private LoggerInterface $logger,
        private BrandService $service,
    ) {}

    #[Route('', name: 'index', methods: ['GET'])]
    public function index(): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();
        return $this->json($this->service->listAll($user->getCompany()));
    }

    #[Route('', name: 'store', methods: ['POST'])]
    public function store(#[MapRequestPayload] BrandInputDTO $dto): JsonResponse
    {
        try {
            /** @var User $user */
            $user = $this->getUser();
            return $this->json($this->service->create($dto, $user->getCompany()), 201);
        } catch (\Exception $e) {
            $this->logger->error('Failed to save brand.', [
                'user_id' => $user->getId(),
                'error' => $e->getMessage()
            ]);
            return $this->json(['message' => 'ERROR_SAVING_BRAND'], 500);
        }
    }
}
