<?php

namespace App\Service\Labor;

use App\DTO\Request\Labor\LaborCategoryInputDTO;
use App\DTO\Response\Labor\LaborCategoryOutputDTO;
use App\Entity\Company;
use App\Entity\Labor\LaborCategory;
use App\Enum\Labor\LaborCategoryStatus;
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

        $category = $this->mapper->toEntity($dto, $company, $category);

        $this->entityManager->flush();

        return $this->mapper->toOutput($category);
    }

    public function delete(int $id, Company $company): void
    {
        $category = $this->repository->findOneBy(['id' => $id, 'company' => $company]);

        $this->entityManager->remove($category);
        $this->entityManager->flush();
    }

    /**
     * Cria as categorias padrão para uma nova empresa com cores e ícones distintos.
     */
    public function createDefaultCategories(Company $company): void
    {
        $defaults = [
            'Instalação' => [
                'color' => '#3b82f6',
                'icon'  => 'wrench'
            ],
            'Construção' => [
                'color' => '#10b981',
                'icon'  => 'hammer'
            ],
            'Manutenção' => [
                'color' => '#f59e0b',
                'icon'  => 'settings'
            ],
            'Consultoria' => [
                'color' => '#8b5cf6',
                'icon'  => 'message-square'
            ],
            'Medição' => [
                'color' => '#06b6d4',
                'icon'  => 'ruler'
            ],
        ];

        foreach ($defaults as $name => $config) {
            $dto = new LaborCategoryInputDTO(
                name: $name,
                parentId: null,
                color: $config['color'],
                icon: $config['icon'],
                status: LaborCategoryStatus::ACTIVE
            );

            $category = $this->mapper->toEntity($dto, $company);
            $this->entityManager->persist($category);
        }

        $this->entityManager->flush();
    }
}
