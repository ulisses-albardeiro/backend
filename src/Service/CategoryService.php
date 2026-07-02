<?php

namespace App\Service;

use App\Entity\Company;
use App\Entity\Category;
use App\Enum\TransactionType;
use App\Mapper\CategoryMapper;
use App\DTO\Request\CategoryInputDTO;
use App\Repository\CategoryRepository;
use App\DTO\Response\CategoryOutputDTO;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class CategoryService
{
    public function __construct(
        private CategoryMapper $mapper,
        private EntityManagerInterface $em,
        private CategoryRepository $repository
    ) {}

    /**
     * @return CategoryOutputDTO[]
     */
    public function listAllByCompany(Company $company): array
    {
        $categories = $this->repository->findCategoryTree($company);
        return array_map(fn(Category $c) => $this->mapper->toOutputDTO($c), $categories);
    }

    public function getByIdAndCompany(int $id, Company $company): CategoryOutputDTO
    {
        $category = $this->repository->findOneBy([
            'id' => $id,
            'company' => $company
        ]);

        if (!$category) {
            throw new NotFoundHttpException('CATEGORY_NOT_FOUND');
        }

        return $this->mapper->toOutputDTO($category);
    }

    public function create(CategoryInputDTO $dto, Company $company): CategoryOutputDTO
    {
        $category = $this->mapper->toEntity($dto, $company);

        if ($category->getParent() && $category->getParent()->getType() !== $category->getType()) {
            throw new BadRequestHttpException('INCOMPATIBLE_SUBCATEGORY');
        }

        $this->em->persist($category);
        $this->em->flush();

        return $this->mapper->toOutputDTO($category);
    }

    public function update(int $id, CategoryInputDTO $dto, Company $company): CategoryOutputDTO
    {
        $category = $this->repository->findByIdAndCompany($id, $company);

        if (!$category) {
            throw new NotFoundHttpException('CATEGORY_NOT_FOUND');
        }

        $category = $this->mapper->toEntity($dto, $company, $category);

        if ($category->getParent() && $category->getParent()->getType() !== $category->getType()) {
            throw new BadRequestHttpException('CATEGORY_INCONSISTENCY');
        }

        $this->em->flush();

        return $this->mapper->toOutputDTO($category);
    }

    public function delete(int $id, Company $company): void
    {
        $category = $this->repository->findByIdAndCompany($id, $company);

        if (!$category) {
            throw new NotFoundHttpException('CATEGORY_NOT_FOUND');
        }

        $this->em->remove($category);
        $this->em->flush();
    }

    public function createDefaultCategories(Company $company): void
    {
        $defaults = [
            ['name' => 'Serviços Prestados', 'type' => TransactionType::INCOME, 'color' => '#10b981', 'icon' => 'briefcase'],
            ['name' => 'Venda de Produtos', 'type' => TransactionType::INCOME, 'color' => '#3b82f6', 'icon' => 'shopping-bag'],
            ['name' => 'Outras Receitas', 'type' => TransactionType::INCOME, 'color' => '#06b6d4', 'icon' => 'plus-circle'],
            ['name' => 'Fornecedores e Materiais', 'type' => TransactionType::EXPENSE, 'color' => '#f59e0b', 'icon' => 'package'],
            ['name' => 'Salários e Pró-labore', 'type' => TransactionType::EXPENSE, 'color' => '#8b5cf6', 'icon' => 'users'],
            ['name' => 'Impostos e Taxas', 'type' => TransactionType::EXPENSE, 'color' => '#ef4444', 'icon' => 'file-text'],
            ['name' => 'Aluguel e Contas Fixas', 'type' => TransactionType::EXPENSE, 'color' => '#f97316', 'icon' => 'home'],
            ['name' => 'Marketing e Publicidade', 'type' => TransactionType::EXPENSE, 'color' => '#ec4899', 'icon' => 'megaphone'],
            ['name' => 'Despesas Administrativas', 'type' => TransactionType::EXPENSE, 'color' => '#64748b', 'icon' => 'briefcase'],
        ];

        foreach ($defaults as $config) {
            $this->create(
                new CategoryInputDTO(
                    name: $config['name'],
                    type: $config['type'],
                    color: $config['color'],
                    icon: $config['icon'],
                ),
                $company
            );
        }
    }
}
