<?php

namespace App\Service\Product;

use App\DTO\Request\Product\CategoryInputDTO;
use App\DTO\Response\Product\CategoryOutputDTO;
use App\Entity\Company;
use App\Entity\Product\ProductCategory;
use App\Mapper\Product\ProductCategoryMapper;
use App\Repository\Product\ProductCategoryRepository;
use Doctrine\ORM\EntityManagerInterface;

class ProductCategoryService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ProductCategoryRepository $repository,
        private ProductCategoryMapper $mapper
    ) {}

    public function create(CategoryInputDTO $dto, Company $company): CategoryOutputDTO
    {
        $category = $this->mapper->toEntity($dto, $company);
        $category->setCompany($company);

        $this->entityManager->persist($category);
        $this->entityManager->flush();

        return $this->mapper->toOutput($category);
    }

    /**
     * @return CategoryOutputDTO[]
     */
    public function listAllByCompany(Company $company): array
    {
        $products = $this->repository->findCategoryTree($company);
        return array_map(fn(ProductCategory $p) => $this->mapper->toOutput($p), $products);
    }

    public function update(int $id, CategoryInputDTO $dto, Company $company): CategoryOutputDTO
    {
        $category = $this->repository->findOneBy(['id' => $id, 'company' => $company]);

        if (!$category) {
            throw new \Exception("CATEGORY_NOT_FOUND");
        }

        $category = $this->mapper->toEntity($dto, $company, $category);
        
        $this->entityManager->flush();

        return $this->mapper->toOutput($category);
    }

    public function delete(int $id, Company $company): void
    {
        $category = $this->repository->findOneBy(['id' => $id, 'company' => $company]);

        if (!$category) {
            throw new \Exception("CATEGORY_NOT_FOUND");
        }

        if (!$category->getProducts()->isEmpty()) {
             throw new \Exception("CANNOT_DELETE_CATEGORY_WITH_PRODUCTS");
        }

        $this->entityManager->remove($category);
        $this->entityManager->flush();
    }
}
