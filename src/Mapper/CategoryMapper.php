<?php

namespace App\Mapper;

use App\Entity\Company;
use App\Entity\Category;
use App\DTO\Request\CategoryInputDTO;
use App\Repository\CategoryRepository;
use App\DTO\Response\CategoryOutputDTO;

class CategoryMapper
{
    public function __construct(
        private CategoryRepository $categoryRepository
    ) {}

    public function toEntity(CategoryInputDTO $dto, Company $company, ?Category $category = null): Category
    {
        $category ??= new Category();

        $category->setCompany($company);
        $category->setName($dto->name);
        $category->setType($dto->type);
        $category->setColor($dto->color);
        $category->setIcon($dto->icon);
        $category->setStatus($dto->status);

        if ($dto->parentId) {
            $parent = $this->categoryRepository->findOneBy([
                'id' => $dto->parentId,
                'company' => $company
            ]);
            $category->setParent($parent);
        } else {
            $category->setParent(null);
        }

        return $category;
    }

    public function toOutputDTO(Category $category): CategoryOutputDTO
    {
        $subCategories = [];
        foreach ($category->getSubCategories() as $sub) {
            $subCategories[] = $this->toOutputDTO($sub);
        }

        return new CategoryOutputDTO(
            id: $category->getId(),
            name: $category->getName(),
            type: $category->getType()->value,
            typeLabel: $category->getType()->getLabel(),
            parentId: $category->getParent()?->getId(),
            parentName: $category->getParent()?->getName(),
            color: $category->getColor(),
            icon: $category->getIcon(),
            status: $category->isStatus(),
            subCategories: $subCategories
        );
    }
}
