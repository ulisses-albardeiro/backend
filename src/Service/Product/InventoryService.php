<?php

namespace App\Service\Product;

use App\Entity\Product\InventoryMovement;
use App\Entity\Product\Product;
use App\Enum\Product\InventoryMovementType;
use Doctrine\ORM\EntityManagerInterface;

class InventoryService
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {}

    public function registerMovement(
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
}
