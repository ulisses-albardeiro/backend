<?php

namespace App\Repository;

use App\Entity\Company;
use App\Entity\Category;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @extends ServiceEntityRepository<Category>
 */
class CategoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Category::class);
    }

    public function findByIdAndCompany(int $id, Company $company): ?Category
    {
        $category = $this->findOneBy([
            'id' => $id,
            'company' => $company
        ]);

        return $category;
    }

    public function findCategoryTree(Company $company): array
    {
        return $this->createQueryBuilder('c')
            ->leftJoin('c.subCategories', 's')
            ->addSelect('s')
            ->where('c.company = :company')
            ->andWhere('c.parent IS NULL')
            ->setParameter('company', $company)
            ->orderBy('c.name', 'ASC')
            ->addOrderBy('s.name', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
