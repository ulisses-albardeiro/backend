<?php

namespace App\Repository;

use App\Entity\Company;
use App\Entity\Transaction;
use App\Enum\TransactionStatus;
use App\Enum\TransactionType;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @extends ServiceEntityRepository<Transaction>
 */
class TransactionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Transaction::class);
    }

    public function getTotalByType(Company $company, TransactionType $type, ?TransactionStatus $status = null): int
    {
        $qb = $this->createQueryBuilder('t')
            ->select('SUM(t.amount)')
            ->where('t.company = :company')
            ->andWhere('t.type = :type')
            ->setParameter('company', $company)
            ->setParameter('type', $type);

        if ($status) {
            $qb->andWhere('t.status = :status')
                ->setParameter('status', $status);
        }

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    public function countAll(): int
    {
        return $this->count([]);
    }
}
