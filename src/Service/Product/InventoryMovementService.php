<?php

namespace App\Service\Product;

use App\DTO\Request\Product\InventoryMovementInputDTO;
use App\DTO\Response\Product\InventoryMovementOutputDTO;
use App\Entity\Company;
use App\Entity\Product\InventoryMovement;
use App\Entity\Product\Product;
use App\Enum\Product\InventoryMovementType;
use App\Mapper\Product\InventoryMovementMapper;
use App\Repository\Product\InventoryMovementRepository;
use App\Repository\Product\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;

class InventoryMovementService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private InventoryMovementRepository $inventoryRepository,
        private ProductRepository $productRepository,
        private InventoryMovementMapper $inventoryMapper,
    ) {}

    public function registerFirstMovement(
        Product $product,
        float $quantity,
        InventoryMovementType $type,
        ?string $description = null
    ): void {
        $movement = new InventoryMovement();
        $movement->setCompany($product->getCompany());
        $movement->setProduct($product);
        $movement->setQuantity($quantity);
        $movement->setType($type);
        $movement->setUnitPrice($product->getPurchasePrice());
        $movement->setDescription($description);

        $currentStock = $product->getStockQuantity();
        
        if ($type === InventoryMovementType::INPUT) {
            $product->setStockQuantity($currentStock + $quantity);
        } elseif ($type === InventoryMovementType::OUTPUT) {
            $product->setStockQuantity($currentStock - $quantity);
        }

        $this->entityManager->persist($movement);
        $this->entityManager->persist($product);
        $this->entityManager->flush();
    }

    /**
     * Registra uma movimentação e atualiza o saldo do produto.
     */
    public function registerMovement(InventoryMovementInputDTO $dto): InventoryMovementOutputDTO
    {
        // 1. Buscar as entidades relacionadas (necessário para o Mapper)
        $product = $this->productRepository->find($dto->productId);
        $company = $this->entityManager->getReference(Company::class, $dto->companyId);

        if (!$product) {
            throw new \Exception("Produto não encontrado.");
        }

        return $this->entityManager->wrapInTransaction(function() use ($dto, $product, $company) {
            
            $movement = $this->inventoryMapper->toEntity($dto, $product, $company);

            $this->updateProductStock($product, $dto->quantity, $dto->type);

            $this->entityManager->persist($movement);
            $this->entityManager->flush();

            return $this->inventoryMapper->toOutput($movement);
        });
    }

    /**
     * Lista movimentações convertendo para Output DTO
     * @return InventoryMovementOutputDTO[]
     */
    public function listAll(Company $company): array
    {
        $movements = $this->inventoryRepository->findBy(
            ['company' => $company],
            ['createdAt' => 'DESC']
        );

        return array_map(
            fn($movement) => $this->inventoryMapper->toOutput($movement),
            $movements
        );
    }

    /**
     * Centraliza a regra de cálculo de estoque
     */
    private function updateProductStock(Product $product, float $quantity, InventoryMovementType $type): void
    {
        $currentStock = $product->getStockQuantity();

        $newStock = match ($type) {
            InventoryMovementType::INPUT => $currentStock + $quantity,
            InventoryMovementType::OUTPUT => $currentStock - $quantity,
            default => $currentStock
        };

        if ($newStock < 0) {
            throw new \Exception("Estoque insuficiente para realizar essa saída.");
        }

        $product->setStockQuantity($newStock);
        $this->entityManager->persist($product);
    }
}
