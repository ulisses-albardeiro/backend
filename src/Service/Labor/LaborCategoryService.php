<?php

namespace App\Service\Labor;

use App\DTO\Request\Labor\LaborCategoryInputDTO;
use App\DTO\Response\Labor\LaborCategoryOutputDTO;
use App\Entity\Company;
use App\Entity\Labor\LaborCategory;
use App\Mapper\Labor\LaborCategoryMapper;
use App\Repository\Labor\LaborCategoryRepository;
use Doctrine\ORM\EntityManagerInterface;

class LaborCategoryService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private LaborCategoryRepository $repository,
        private LaborCategoryMapper $mapper
    ) {}

    public function create(LaborCategoryInputDTO $dto, Company $company): LaborCategoryOutputDTO
    {
        $category = $this->mapper->toEntity($dto, $company);
        
        $this->entityManager->persist($category);
        $this->entityManager->flush();

        return $this->mapper->toOutput($category);
    }

    /**
     * @return LaborCategoryOutputDTO[]
     */
    public function listAllByCompany(Company $company): array
    {
        // Utiliza o método de árvore que você já tem no Repository
        $categories = $this->repository->findCategoryTree($company);
        return array_map(fn(LaborCategory $c) => $this->mapper->toOutput($c), $categories);
    }

    /**
     * @return LaborCategoryOutputDTO[]
     */
    public function listAllByStatus(Company $company, string $status): array
    {
        $categories = $this->repository->findCategoryTreeByStatus($company, $status);
        return array_map(fn(LaborCategory $c) => $this->mapper->toOutput($c), $categories);
    }

    public function update(int $id, LaborCategoryInputDTO $dto, Company $company): LaborCategoryOutputDTO
    {
        $category = $this->repository->findOneBy(['id' => $id, 'company' => $company]);

        if (!$category) {
            throw new \Exception("LABOR_CATEGORY_NOT_FOUND");
        }

        // O Mapper atualiza a entidade existente
        $category = $this->mapper->toEntity($dto, $company, $category);
        
        $this->entityManager->flush();

        return $this->mapper->toOutput($category);
    }

    public function delete(int $id, Company $company): void
    {
        $category = $this->repository->findOneBy(['id' => $id, 'company' => $company]);

        if (!$category) {
            throw new \Exception("LABOR_CATEGORY_NOT_FOUND");
        }

        try {
            $this->entityManager->remove($category);
            $this->entityManager->flush();
        } catch (\Exception $e) {
            // Caso existam Labors vinculados a esta categoria e o banco impeça a deleção
            throw new \Exception("CANNOT_DELETE_CATEGORY_IN_USE");
        }
    }
}
