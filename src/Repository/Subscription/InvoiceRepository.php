<?php

namespace App\Repository\Subscription;

use App\Entity\Company;
use App\Entity\Subscription\Invoice;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Invoice>
 */
class InvoiceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Invoice::class);
    }

    public function findOneByAsaasPaymentId(string $asaasPaymentId): ?Invoice
    {
        return $this->findOneBy(['asaasPaymentId' => $asaasPaymentId]);
    }

    public function findByIdAndCompany(int $id, Company $company): ?Invoice
    {
        return $this->findOneBy([
            'id' => $id,
            'company' => $company,
        ]);
    }

    /**
     * @return Invoice[]
     */
    public function findByCompany(Company $company): array
    {
        return $this->findBy(['company' => $company], ['dueDate' => 'DESC']);
    }

    //    /**
    //     * @return Invoice[] Returns an array of Invoice objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('i')
    //            ->andWhere('i.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('i.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Invoice
    //    {
    //        return $this->createQueryBuilder('i')
    //            ->andWhere('i.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
