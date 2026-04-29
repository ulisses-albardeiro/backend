<?php

namespace App\Controller\Product;

use App\DTO\Request\Product\CategoryInputDTO;
use App\Service\Product\ProductCategoryService;
use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('IS_AUTHENTICATED_FULLY')]
#[Route('/api/product-category', name: 'api_product_category_', format: 'json')]
final class ProductCategoryController extends AbstractController
{
    public function __construct(
        private LoggerInterface $logger,
        private ProductCategoryService $service,
    ) {}

    #[Route('', name: 'index', methods: ['GET'])]
    public function index(): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();
        return $this->json($this->service->listAllByCompany($user->getCompany()));
    }

    #[Route('/active', name: 'active', methods: ['GET'])]
    public function indexActive(): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();
        return $this->json($this->service->listAllByStatus($user->getCompany(), 'active'));
    }

    #[Route('', name: 'store', methods: ['POST'])]
    public function store(#[MapRequestPayload] CategoryInputDTO $dto): JsonResponse
    {
        try {
            /** @var User $user */
            $user = $this->getUser();
            return $this->json($this->service->create($dto, $user->getCompany()), 201);
        } catch (\Exception $e) {
            $this->logger->error('Falha ao salvar categoria de produto.', [
                'user_id' => $user->getId(),
                'error' => $e->getMessage()
            ]);
            return $this->json(['message' => 'Houve um erro inesperado.'], 500);
        }
    }

    #[Route('/{id}', name: 'update', methods: ['PUT'], requirements: ['id' => '\d+'])]
    public function update(int $id, #[MapRequestPayload] CategoryInputDTO $dto): JsonResponse
    {
        try {
            /** @var User $user */
            $user = $this->getUser();
            return $this->json($this->service->update($id, $dto, $user->getCompany()));
        } catch (\Exception $e) {
            $this->logger->error('Falha ao atualizar categoria de produto.', [
                'id' => $id,
                'error' => $e->getMessage()
            ]);
            return $this->json(['message' => 'Houve um erro inesperado.'], 500);
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
        } catch (ForeignKeyConstraintViolationException $e) {
            return $this->json([
                'message' => "Não é possível excluir Essa categoria, pois existem produtos associados a ela."
            ], 409);
        } catch (\Exception $e) {
            $this->logger->error('Erro ao deletar Categoria de Produto.', [
                'id' => $id,
                'error' => $e->getMessage()
            ]);
            return $this->json([
                'message' => 'Houve um erro Inesperado.'
            ], 500);
        }
    }
}
