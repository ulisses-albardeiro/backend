<?php

namespace App\Controller\Product;

use App\DTO\Request\Product\BrandInputDTO;
use App\Service\Product\BrandService;
use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
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

    #[Route('/active', name: 'active', methods: ['GET'])]
    public function indexByActive(): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();
        return $this->json($this->service->listAllActive($user->getCompany()));
    }

    #[Route('', name: 'store', methods: ['POST'])]
    public function store(
        #[MapRequestPayload] BrandInputDTO $dto,
        Request $request
    ): JsonResponse {
        try {
            $logoFile = $request->files->get('logo');
            /** @var User $user */
            $user = $this->getUser();

            return $this->json($this->service->create($dto, $user->getCompany(), $logoFile), 201);
        } catch (\Exception $e) {
            return $this->json(['message' => 'ERROR_SAVING_BRAND'], 500);
        }
    }

    #[Route('/{id}', name: 'update', methods: ['PUT'])]
    public function update(
        int $id,
        #[MapRequestPayload] BrandInputDTO $dto,
        Request $request
    ): JsonResponse {
        try {
            $logoFile = $request->files->get('logo');
            /** @var User $user */
            $user = $this->getUser();

            return $this->json($this->service->update($id, $dto, $user->getCompany(), $logoFile));
        } catch (\Exception $e) {
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
        } catch (ForeignKeyConstraintViolationException $e) {
            return $this->json([
                'message' => "Não é possível excluir Essa categoria, pois existem produtos associados a ela."
            ], 409);
        } catch (\Exception $e) {
            $this->logger->error('Failed to delete brand.', [
                'brand_id' => $id,
                'error' => $e->getMessage()
            ]);
            return $this->json(['message' => $e->getMessage()], 400);
        }
    }
}
