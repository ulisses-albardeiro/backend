<?php

namespace App\Controller\Product;

use App\DTO\Request\Product\ProductInputDTO;
use App\Service\Product\ProductService;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('IS_AUTHENTICATED_FULLY')]
#[Route('/api/product', name: 'api_product_', format: 'json')]
final class ProductController extends AbstractController
{
    public function __construct(
        private LoggerInterface $logger,
        private ProductService $service,
    ) {}

    #[Route('', name: 'index', methods: ['GET'])]
    public function index(): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();
        return $this->json($this->service->listAll($user->getCompany()));
    }

    #[Route('/{id}', name: 'show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(int $id): JsonResponse
    {
        try {
            /** @var User $user */
            $user = $this->getUser();
            return $this->json($this->service->getById($id, $user->getCompany()));
        } catch (NotFoundHttpException $e) {
            return $this->json(['message' => $e->getMessage()], 404);
        }
    }

    #[Route('', name: 'store', methods: ['POST'])]
    public function store(
        #[MapRequestPayload] ProductInputDTO $dto,
        Request $request
    ): JsonResponse {
        try {
            /** @var User $user */
            $user = $this->getUser();

            $imageFiles = $request->files->get('images') ?? [];

            $product = $this->service->create($dto, $user->getCompany(), $imageFiles);

            return $this->json($product, 201);
        } catch (BadRequestHttpException $e) {
            return $this->json(['message' => $e->getMessage()], 400);
        } catch (\Exception $e) {
            $this->logger->error('Failed to save product.', [
                'user_id' => $user->getId() ?? 'unknown',
                'error' => $e->getMessage()
            ]);
            return $this->json(['message' => 'ERROR_SAVING_PRODUCT'], 500);
        }
    }

    #[Route('/{id}', name: 'update', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function update(
        int $id,
        #[MapRequestPayload] ProductInputDTO $dto,
        Request $request
    ): JsonResponse {
        try {
            /** @var User $user */
            $user = $this->getUser();

            $imageFiles = $request->files->get('images') ?? [];

            $product = $this->service->update($id, $dto, $user->getCompany(), $imageFiles);

            return $this->json($product, 200);
        } catch (NotFoundHttpException $e) {
            return $this->json(['message' => $e->getMessage()], 404);
        } catch (\Exception $e) {
            $this->logger->error('Update product error', [
                'product_id' => $id,
                'user_id' => $user->getId() ?? 'unknown',
                'error' => $e->getMessage()
            ]);
            return $this->json(['message' => 'ERROR_UPDATING_PRODUCT'], 500);
        }
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'], requirements: ['id' => '\d+'])]
    public function delete(int $id): JsonResponse
    {
        try {
            /** @var User $user */
            $user = $this->getUser();
            $this->service->delete($id, $user->getCompany());

            return $this->json(null, 204);
        } catch (NotFoundHttpException $e) {
            return $this->json(['message' => $e->getMessage()], 404);
        } catch (\Exception $e) {
            $this->logger->error('Delete product error', [
                'product_id' => $id,
                'error' => $e->getMessage()
            ]);
            return $this->json(['message' => 'ERROR_DELETING_PRODUCT'], 500);
        }
    }
}
