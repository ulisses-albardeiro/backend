<?php

namespace App\Mapper\Labor;

use App\DTO\Request\Labor\LaborCategoryInputDTO;
use App\DTO\Response\Labor\LaborCategoryOutputDTO;
use App\Entity\Company;
use App\Entity\Labor\LaborCategory;
use App\Repository\Labor\LaborCategoryRepository;

class LaborCategoryMapper
{
    public function __construct(
        private LaborCategoryRepository $categoryRepository
    ) {}

    public function toEntity(LaborCategoryInputDTO $dto, Company $company, ?LaborCategory $entity = null): LaborCategory
    {
        $entity = $entity ?? new LaborCategory();
        $entity->setName($dto->name);
        $entity->setCompany($company);
        $entity->setColor($dto->color);
        $entity->setIcon($dto->icon);
        $entity->setStatus($dto->status);

        if ($dto->parentId) {
            $parent = $this->categoryRepository->findOneBy([
                'id' => $dto->parentId,
                'company' => $company
            ]);
            $entity->setParent($parent);
        } else {
            $entity->setParent(null);
        }

        return $entity;
    }

    public function toOutput(LaborCategory $entity): LaborCategoryOutputDTO
    {
        $subCategories = [];
        foreach ($entity->getSubCategories() as $sub) {
            $subCategories[] = $this->toOutput($sub);
        }

        return new LaborCategoryOutputDTO(
            id: $entity->getId(),
            name: $entity->getName(),
            parentId: $entity->getParent()?->getId(),
            parentName: $entity->getParent()?->getName(),
            color: $entity->getColor(),
            icon: $entity->getIcon(),
            statusLabel: $entity->getStatus()->getLabel(),
            status: $entity->getStatus()->value,
            subCategories: $subCategories,
        );
    }
}
