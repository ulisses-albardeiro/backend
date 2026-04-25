<?php

namespace App\Mapper\Product;

use App\DTO\Request\Product\SupplierInputDTO;
use App\DTO\Response\Product\SupplierOutputDTO;
use App\Entity\Product\Supplier;

class SupplierMapper
{
    /**
     * Converte DTO em Entidade
     */
    public function toEntity(SupplierInputDTO $dto, ?Supplier $entity = null): Supplier
    {
        $entity = $entity ?? new Supplier();
        
        $entity->setName($dto->name);
        $entity->setDocument($dto->document);
        $entity->setPersonType($dto->type);
        $entity->setEmail($dto->email);
        $entity->setPhone($dto->phone);
        $entity->setStatus($dto->status);

        return $entity;
    }

    /**
     * Converte Entidade em DTO
     */
    public function toOutput(Supplier $entity): SupplierOutputDTO
    {
        return new SupplierOutputDTO(
            id: $entity->getId(),
            name: $entity->getName(),
            document: $entity->getDocument(),
            typeLabel: $entity->getPersonType()->value === 'F' ? 'Pessoa Física' : 'Pessoa Jurídica',
            email: $entity->getEmail(),
            phone: $entity->getPhone(),
            statusLabel: $entity->getStatus()->getLabel()
        );
    }
}
