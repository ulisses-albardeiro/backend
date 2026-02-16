<?php

namespace App\Mapper;

use App\Entity\Company;
use App\Entity\Customer;
use App\Entity\Category;
use App\Entity\Transaction;
use App\Enum\TransactionType;
use App\Enum\TransactionStatus;
use App\DTO\Request\TransactionInputDTO;
use App\DTO\Response\TransactionOutputDTO;

class TransactionMapper
{
    public function toEntity(
        TransactionInputDTO $dto,
        Company $company,
        Category $category,
        ?Customer $customer = null,
        ?Transaction $transaction = null
    ): Transaction {
        $transaction ??= new Transaction();

        $transaction->setDescription($dto->description);
        $transaction->setAmount($dto->amount);
        $transaction->setDate(new \DateTimeImmutable($dto->date));
        $transaction->setType(TransactionType::from($dto->type));
        $transaction->setStatus(TransactionStatus::from($dto->status));
        $transaction->setCategory($category);
        $transaction->setCustomer($customer);
        $transaction->setCompany($company);

        return $transaction;
    }

    public function toOutputDTO(Transaction $transaction): TransactionOutputDTO
    {
        return new TransactionOutputDTO(
            id: $transaction->getId(),
            description: $transaction->getDescription(),
            amount: $transaction->getAmount(),
            amountFormatted: $transaction->getAmount() / 100,
            date: $transaction->getDate(),
            type: $transaction->getType()->value,
            typeLabel: $transaction->getType()->getLabel(),
            status: $transaction->getStatus()->value,
            statusLabel: $transaction->getStatus()->getLabel(),
            categoryId: $transaction->getCategory()->getId(),
            categoryName: $transaction->getCategory()->getName(),
            categoryColor: $transaction->getCategory()->getColor(),
            customerId: $transaction->getCustomer()?->getId(),
            customerName: $transaction->getCustomer()?->getName(),
            createdAt: $transaction->getDate(),
        );
    }
}
