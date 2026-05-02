<?php

namespace App\Mapper\Labor;

use App\DTO\Request\Labor\LaborInputDTO;
use App\DTO\Response\Labor\LaborOutputDTO;
use App\Entity\Labor\Labor;
use App\Repository\Labor\LaborCategoryRepository;

class LaborMapper
{
    public function __construct(
        private LaborCategoryRepository $categoryRepository,
        private LaborCategoryMapper $categoryMapper
    ) {}

    public function toEntity(LaborInputDTO $dto, ?Labor $entity = null): Labor
    {
        $entity = $entity ?? new Labor();

        $entity->setName($dto->name);
        $entity->setDescription($dto->description);
        $entity->setSalePrice($dto->salePrice);
        $entity->setUnit($dto->unit);
        $entity->setStatus($dto->status);

        $category = $this->categoryRepository->find($dto->categoryId);
        if ($category) {
            $entity->setCategory($category);
        }

        return $entity;
    }

    public function toOutput(Labor $entity): LaborOutputDTO
    {
        return new LaborOutputDTO(
            id: $entity->getId(),
            name: $entity->getName(),
            category: $this->categoryMapper->toOutput($entity->getCategory()),
            description: $entity->getDescription(),
            salePrice: $entity->getSalePrice(),
            unitLabel: $entity->getUnit()->getLabel(),
            unit: $entity->getUnit()->value,
            statusLabel: $entity->getStatus()->getLabel(),
            status: $entity->getStatus()->value,
            createdAt: $entity->getCreatedAt(),
            updatedAt: $entity->getUpdatedAt()
        );
    }
}
