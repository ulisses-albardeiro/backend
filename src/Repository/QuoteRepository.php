<?php

namespace App\Repository;

use App\Entity\Company;
use App\Entity\Quote;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Quote>
 */
class QuoteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Quote::class);
    }

    public function findByIdAndCompany(int $id, Company $company): ?Quote
    {
        $quote = $this->createQueryBuilder('q')
            ->join('q.customer', 'c')
            ->where('q.id = :id')
            ->andWhere('c.company = :company')
            ->setParameter('id', $id)
            ->setParameter('company', $company)
            ->getQuery()
            ->getOneOrNullResult();

        return $quote;
    }

    public function countAll(): int
    {
        return $this->count([]);
    }
}
