<?php

namespace App\Service\Product;

use App\DTO\Request\Product\ProductInputDTO;
use App\DTO\Response\Product\ProductOutputDTO;
use App\Entity\Company;
use App\Enum\Product\InventoryMovementType;
use App\Mapper\Product\ProductMapper;
use App\Repository\Product\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ProductService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ProductRepository $productRepository,
        private ProductMapper $productMapper,
        private InventoryService $inventoryService
    ) {}

    public function create(ProductInputDTO $dto, Company $company): ProductOutputDTO
    {
        $product = $this->productMapper->toEntity($dto);
        $product->setCompany($company);

        $this->entityManager->persist($product);
        $this->entityManager->flush();

        if ($dto->initialStock > 0) {
            $this->inventoryService->registerMovement(
                $product,
                $dto->initialStock,
                InventoryMovementType::INPUT,
                'Estoque inicial no cadastro do produto'
            );
        }

        return $this->productMapper->toOutput($product);
    }

    public function update(int $id, ProductInputDTO $dto, Company $company): ProductOutputDTO
    {
        $product = $this->productRepository->findOneBy(['id' => $id, 'company' => $company]);

        if (!$product) {
            throw new \Exception("Produto não encontrado.");
        }

        $product = $this->productMapper->toEntity($dto, $product);
        $this->entityManager->flush();

        return $this->productMapper->toOutput($product);
    }

    public function listAll(Company $company): array
    {
        $products = $this->productRepository->findBy(['company' => $company]);
        return array_map(fn($p) => $this->productMapper->toOutput($p), $products);
    }

    public function getById(int $id, Company $company): ProductOutputDTO
    {
        $product = $this->productRepository->findOneBy([
            'id' => $id,
            'company' => $company
        ]);

        if (!$product) {
            throw new NotFoundHttpException('PRODUCT_NOT_FOUND');
        }

        return $this->productMapper->toOutput($product);
    }

    public function delete(int $id, Company $company): void
    {
        $product = $this->productRepository->findOneBy([
            'id' => $id,
            'company' => $company
        ]);

        if (!$product) {
            throw new NotFoundHttpException('PRODUCT_NOT_FOUND');
        }

        try {
            $this->entityManager->remove($product);
            $this->entityManager->flush();
        } catch (\Exception $e) {
            // Caso existam chaves estrangeiras (ex: item de nota fiscal) 
            // que impeçam a exclusão física
            throw new \Exception('CANNOT_DELETE_PRODUCT_IN_USE');
        }
    }
}
