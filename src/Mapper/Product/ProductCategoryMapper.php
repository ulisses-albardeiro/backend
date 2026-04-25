<?php

namespace App\Mapper\Product;

use App\DTO\Request\Product\CategoryInputDTO;
use App\DTO\Response\Product\CategoryOutputDTO;
use App\Entity\Company;
use App\Entity\Product\ProductCategory;
use App\Repository\Product\ProductCategoryRepository;

class ProductCategoryMapper
{
    public function __construct(
        private ProductCategoryRepository $categoryRepository
    ) {}

    public function toEntity(CategoryInputDTO $dto, Company $company, ?ProductCategory $entity = null): ProductCategory
    {
        $entity = $entity ?? new ProductCategory();
        $entity->setName($dto->name);
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

    public function toOutput(ProductCategory $entity): CategoryOutputDTO
    {
        $subCategories = [];
        foreach ($entity->getSubCategories() as $sub) {
            $subCategories[] = $this->toOutput($sub);
        }

        return new CategoryOutputDTO(
            id: $entity->getId(),
            name: $entity->getName(),
            parentId: $entity->getParent()?->getId(),
            parentName: $entity->getParent()?->getName(),
            color: $entity->getColor(),
            icon: $entity->getIcon(),
            statusLabel: $entity->getStatus()->getLabel(),
            statusCode: $entity->getStatus()->value,
            subCategories: $subCategories,
        );
    }
}
