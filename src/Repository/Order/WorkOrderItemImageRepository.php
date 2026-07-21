<?php

namespace App\Repository\Order;

use App\Entity\Order\WorkOrderItemImage;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<WorkOrderItemImage>
 */
class WorkOrderItemImageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, WorkOrderItemImage::class);
    }
}
