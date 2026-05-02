<?php

namespace App\Repository\Labor;

use App\Entity\Company;
use App\Entity\Labor\LaborCategory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<LaborCategory>
 */
class LaborCategoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LaborCategory::class);
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

    public function findCategoryTreeByStatus(Company $company, string $status): array
    {
        return $this->createQueryBuilder('c')
            ->leftJoin('c.subCategories', 's')
            ->addSelect('s')
            ->where('c.company = :company')
            ->andWhere('c.status = :status')
            ->andWhere('c.parent IS NULL')
            ->setParameter('company', $company)
            ->setParameter('status', $status)
            ->orderBy('c.name', 'ASC')
            ->addOrderBy('s.name', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
