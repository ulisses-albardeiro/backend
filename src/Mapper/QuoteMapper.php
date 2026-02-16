<?php

namespace App\Mapper;

use App\Entity\Quote;
use App\Entity\Company;
use App\Entity\QuoteItem;
use App\Enum\QuoteStatus;
use App\Enum\DiscountType;
use App\DTO\Request\QuoteInputDTO;
use App\DTO\Response\QuoteOutputDTO;
use App\Repository\CustomerRepository;
use App\DTO\Response\QuoteItemOutputDTO;

class QuoteMapper
{
    public function __construct(
        private CustomerRepository $customerRepository
    ) {}

    public function toEntity(QuoteInputDTO $dto, Company $company, ?Quote $quote = null): Quote
    {
        $quote = $quote ?? new Quote();

        $customer = $this->customerRepository->findOneBy([
            'id' => $dto->customer_id,
            'company' => $company
        ]);
        
        $quote->setCompany($company);
        $quote->setCustomer($customer);
        $quote->setDate(new \DateTimeImmutable($dto->date));
        $quote->setDueDate(new \DateTimeImmutable($dto->due_date));
        $quote->setDescription($dto->description);
        $quote->setNotes($dto->notes);
        
        $quote->setDiscountType(DiscountType::from($dto->discount_type));
        $quote->setDiscountValue($dto->discount_value);
        $quote->setShippingValue($dto->shipping_value);

        if ($quote->getId()) {
            foreach ($quote->getQuoteItems() as $oldItem) {
                $quote->removeQuoteItem($oldItem);
            }
        }

        foreach ($dto->items as $itemDto) {
            $item = new QuoteItem();
            $item->setDescription($itemDto->description);
            $item->setQuantity($itemDto->quantity);
            $item->setUnitPrice($itemDto->unit_price);
            
            $quote->addQuoteItem($item);
        }

        return $quote;
    }

    public function toOutputDTO(Quote $quote): QuoteOutputDTO
    {
        $items = array_map(function (QuoteItem $item) {
            return new QuoteItemOutputDTO(
                id: $item->getId(),
                description: $item->getDescription(),
                quantity: $item->getQuantity(),
                unitPrice: $item->getUnitPrice(),
                totalPrice: $item->getTotalPrice()
            );
        }, $quote->getQuoteItems()->toArray());

        return new QuoteOutputDTO(
            id: $quote->getId(),
            code: $quote->getCode(),
            companyId: $quote->getCompany()->getId(),
            customerId: $quote->getCustomer()->getId(),
            customerName: $quote->getCustomer()->getName(),
            status: $quote->getStatus()->value,
            statusLabel: $quote->getStatus()->getLabel(),
            statusColor: $quote->getStatus()->getColor(),
            date: $quote->getDate()->format('Y-m-d'),
            dueDate: $quote->getDueDate()->format('Y-m-d'),
            subtotal: $quote->getSubtotal(),
            discountType: $quote->getDiscountType()->value,
            discountValue: $quote->getDiscountValue(),
            shippingValue: $quote->getShippingValue(),
            totalAmount: $quote->getTotalAmount(),
            description: $quote->getDescription(),
            notes: $quote->getNotes(),
            items: $items
        );
    }
}
