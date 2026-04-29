<?php

namespace App\Mapper\Product;

use App\DTO\Request\Product\InventoryMovementInputDTO;
use App\DTO\Response\Product\InventoryMovementOutputDTO;
use App\Entity\Company;
use App\Entity\Product\InventoryMovement;
use App\Entity\Product\Product;

class InventoryMovementMapper
{
    public function toEntity(
        InventoryMovementInputDTO $dto, 
        Product $product, 
        Company $company, 
        ?InventoryMovement $entity = null
    ): InventoryMovement {
        $entity = $entity ?? new InventoryMovement();
        
        $entity->setProduct($product);
        $entity->setCompany($company);
        $entity->setType($dto->type);
        $entity->setQuantity($dto->quantity);
        $entity->setUnitPrice($dto->unitPrice);
        $entity->setSalePrice($dto->salePrice);
        $entity->setDescription($dto->description);

        return $entity;
    }

    public function toOutput(InventoryMovement $entity): InventoryMovementOutputDTO
    {
        return new InventoryMovementOutputDTO(
            id: $entity->getId(),
            productId: $entity->getProduct()->getId(),
            productName: $entity->getProduct()->getName(),
            typeLabel: $entity->getType()->getLabel(),
            typeValue: $entity->getType()->value,
            quantity: $entity->getQuantity(),
            unitPrice: $entity->getUnitPrice(),
            salePrice: $entity->getSalePrice(),
            description: $entity->getDescription(),
            createdAt: $entity->getCreatedAt(),
        );
    }
}
