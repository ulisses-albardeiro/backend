<?php

namespace App\Mapper\Product;

use App\DTO\Request\Product\BrandInputDTO;
use App\DTO\Response\Product\BrandOutputDTO;
use App\Entity\Product\Brand;

class BrandMapper
{
    public function toEntity(BrandInputDTO $dto, ?Brand $entity = null): Brand
    {
        $entity = $entity ?? new Brand();
        $entity->setName($dto->name);
        $entity->setLogo($dto->logo);
        $entity->setStatus($dto->status);

        return $entity;
    }

    public function toOutput(Brand $entity): BrandOutputDTO
    {
        return new BrandOutputDTO(
            id: $entity->getId(),
            name: $entity->getName(),
            logo: $entity->getLogo(),
            statusLabel: $entity->getStatus()->getLabel(),
            statusCode: $entity->getStatus()->value
        );
    }
}
