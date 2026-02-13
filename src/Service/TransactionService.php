<?php

namespace App\Service;

use App\Entity\Company;
use App\Entity\Transaction;
use App\Mapper\TransactionMapper;
use App\Repository\CategoryRepository;
use App\Repository\CustomerRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\DTO\Request\TransactionInputDTO;
use App\Repository\TransactionRepository;
use App\DTO\Response\TransactionOutputDTO;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class TransactionService
{
    public function __construct(
        private TransactionMapper $mapper,
        private EntityManagerInterface $em,
        private CustomerRepository $customerRepo,
        private CategoryRepository $categoryRepo,
        private TransactionRepository $transactionRepo,
    ) {}

    public function listAllByCompany(Company $company): array
    {
        $transactions = $this->transactionRepo->findBy(
            ['company' => $company],
            ['date' => 'DESC']
        );

        return array_map(
            fn(Transaction $t) => $this->mapper->toOutputDTO($t),
            $transactions
        );
    }

    public function getByIdAndCompany(int $id, Company $company): TransactionOutputDTO
    {
        $transaction = $this->transactionRepo->findOneBy([
            'id' => $id,
            'company' => $company
        ]);

        if (!$transaction) {
            throw new NotFoundHttpException('TRANSLACTION_NOT_FOUND');
        }

        return $this->mapper->toOutputDTO($transaction);
    }

    public function create(TransactionInputDTO $dto, Company $company): TransactionOutputDTO
    {
        $category = $this->categoryRepo->findOneBy([
            'id' => $dto->categoryId, 
            'company' => $company
        ]);

        if (!$category) {
            throw new BadRequestHttpException('CATEGORY_NOT_FOUND_OR_NOT_APPLICABLE');
        }

        $customer = null;
        if ($dto->customerId) {
            $customer = $this->customerRepo->findOneBy([
                'id' => $dto->customerId, 
                'company' => $company
            ]);
        }

        $transaction = $this->mapper->toEntity($dto, $company, $category, $customer);
        
        $this->em->persist($transaction);
        $this->em->flush();

        return $this->mapper->toOutputDTO($transaction);
    }

    public function update(int $id, TransactionInputDTO $dto, Company $company): TransactionOutputDTO
    {
        $transaction = $this->transactionRepo->findOneBy([
            'id' => $id,
            'company' => $company
        ]);

        if (!$transaction) {
            throw new NotFoundHttpException('TRANSLACTION_NOT_FOUND');
        }

        $category = $this->categoryRepo->findOneBy([
            'id' => $dto->categoryId,
            'company' => $company
        ]);

        if (!$category) {
            throw new BadRequestHttpException('TRANSLACTION_NOT_FOUND');
        }

        $customer = null;
        if ($dto->customerId) {
            $customer = $this->customerRepo->findOneBy([
                'id' => $dto->customerId,
                'company' => $company
            ]);
        }

        $updatedTransaction = $this->mapper->toEntity($dto, $company, $category, $customer, $transaction);
        
        $this->em->flush();

        return $this->mapper->toOutputDTO($updatedTransaction);
    }

    public function delete(int $id, Company $company): void
    {
        $transaction = $this->transactionRepo->findOneBy([
            'id' => $id,
            'company' => $company
        ]);
        
        if (!$transaction) {
            throw new NotFoundHttpException('TRANSACTION_NOT_FOUND');
        }

        $this->em->remove($transaction);
        $this->em->flush();
    }
}
