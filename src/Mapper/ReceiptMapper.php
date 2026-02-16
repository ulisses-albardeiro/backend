<?php

namespace App\Mapper;

use App\Entity\Receipt;
use App\Entity\Quote;
use App\Entity\Customer;
use App\Entity\Company;
use App\DTO\Request\ReceiptInputDTO;
use App\DTO\Response\ReceiptOutputDTO;

class ReceiptMapper
{
    public function toEntity(ReceiptInputDTO $dto, Company $company, Customer $customer, ?Quote $quote = null, ?Receipt $receipt = null): Receipt
    {
        $receipt = $receipt ?? new Receipt();

        $receipt->setAmount($dto->amount);
        $receipt->setPaymentDate($dto->paymentDate);
        $receipt->setPaymentMethod($dto->paymentMethod);
        $receipt->setDescription($dto->description);
        $receipt->setCustomer($customer);
        $receipt->setCompany($company);
        $receipt->setQuote($quote);
        $receipt->setNotes($dto->notes);

        return $receipt;
    }

    public function toOutputDTO(Receipt $receipt): ReceiptOutputDTO
    {
        return new ReceiptOutputDTO(
            id: $receipt->getId(),
            code: $receipt->getCode(),
            amount: $receipt->getAmount(), 
            paymentDate: $receipt->getPaymentDate(),
            paymentMethod: $receipt->getPaymentMethod(),
            paymentMethodLabel: $receipt->getPaymentMethod()->getLabel(),
            description: $receipt->getDescription(),
            status: $receipt->getStatus(),
            statusLabel: $receipt->getStatus()->getLabel(),
            quoteId: $receipt->getQuote()?->getId(),
            customerId: $receipt->getCustomer()->getId(),
            customerName: $receipt->getCustomer()->getName(),
            notes: $receipt->getNotes(),
            createdAt: $receipt->getCreatedAt(),
            updatedAt: $receipt->getUpdatedAt()
        );
    }
}
