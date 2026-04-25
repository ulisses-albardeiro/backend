<?php

namespace App\Mapper\Product;

use App\DTO\Request\Product\BrandInputDTO;
use App\DTO\Response\Product\BrandOutputDTO;
use App\Entity\Product\Brand;
use DateTimeImmutable;
use DateTimeZone;

class BrandMapper
{
    public function toEntity(BrandInputDTO $dto, ?Brand $entity = null): Brand
    {
        $entity = $entity ?? new Brand();
        $entity->setName($dto->name);
        $entity->setLogo($dto->logo);
        $entity->setStatus($dto->status);
        $entity->setCreatedAt(new DateTimeImmutable('now', new DateTimeZone('America/Sao_Paulo')));

        return $entity;
    }

    public function toOutput(Brand $entity, string $urlImage): BrandOutputDTO
    {
        return new BrandOutputDTO(
            id: $entity->getId(),
            name: $entity->getName(),
            logo: $urlImage,
            statusLabel: $entity->getStatus()->getLabel(),
            statusCode: $entity->getStatus()->value
        );
    }
}
