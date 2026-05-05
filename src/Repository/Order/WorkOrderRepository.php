<?php

namespace App\Repository\Order;

use App\Entity\Company;
use App\Entity\Order\WorkOrder;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<WorkOrder>
 */
class WorkOrderRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, WorkOrder::class);
    }

    public function findByIdAndCompany(int $id, Company $company): ?WorkOrder
    {
        $order = $this->createQueryBuilder('q')
            ->join('q.customer', 'c')
            ->where('q.id = :id')
            ->andWhere('c.company = :company')
            ->setParameter('id', $id)
            ->setParameter('company', $company)
            ->getQuery()
            ->getOneOrNullResult();

        return $order;
    }
}
