<?php

namespace App\Mapper\Quote;

use App\Entity\Quote\Quote;
use App\Entity\Company;
use App\Entity\Quote\QuoteItem;
use App\Entity\Product\Product;
use App\Entity\Labor\Labor;
use App\Enum\DiscountType;
use App\DTO\Request\Quote\QuoteInputDTO;
use App\DTO\Response\Quote\QuoteOutputDTO;
use App\DTO\Response\Quote\QuoteItemOutputDTO;
use App\Repository\CustomerRepository;
use Doctrine\ORM\EntityManagerInterface;

class QuoteMapper
{
    public function __construct(
        private CustomerRepository $customerRepository,
        private EntityManagerInterface $em
    ) {}

    public function toEntity(QuoteInputDTO $dto, Company $company, ?Quote $quote = null): Quote
    {
        $quote = $quote ?? new Quote();

        $customer = $this->customerRepository->findOneBy([
            'id' => $dto->customerId,
            'company' => $company
        ]);
        
        $quote->setCompany($company);
        $quote->setCustomer($customer);
        $quote->setDate($dto->date);
        $quote->setDueDate($dto->dueDate);
        $quote->setDescription($dto->description);
        $quote->setNotes($dto->notes);
        $quote->setStatus($dto->status);
        
        $quote->setDiscountType(DiscountType::from($dto->discountType));
        $quote->setDiscountValue($dto->discountValue);
        $quote->setShippingValue($dto->shippingValue);
        $quote->setInternalNotes($dto->internalNotes);

        // Limpa itens existentes para re-inserção (Update)
        if ($quote->getId()) {
            foreach ($quote->getQuoteItems() as $oldItem) {
                $quote->removeQuoteItem($oldItem);
            }
        }

        foreach ($dto->items as $itemDto) {
            $item = new QuoteItem();
            $item->setDescription($itemDto->description);
            $item->setQuantity($itemDto->quantity);
            $item->setUnitPrice($itemDto->unitPrice);
            
            // Tratamento de Mão de Obra
            if ($itemDto->laborId > 0) {
                // getReference cria um Proxy sem carregar do banco, apenas com o ID
                $laborProxy = $this->em->getReference(Labor::class, $itemDto->laborId);
                $item->setLabor($laborProxy);
                $item->setProduct(null);
            }

            // Tratamento de Produto
            if ($itemDto->productId > 0) {
                $productProxy = $this->em->getReference(Product::class, $itemDto->productId);
                $item->setProduct($productProxy);
                $item->setLabor(null);
            }
            
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
                totalPrice: $item->getTotalPrice(),
                // Retorna apenas o ID para o DTO de saída
                laborId: $item->getLabor()?->getId(),
                productId: $item->getProduct()?->getId()
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
            date: $quote->getDate(),
            dueDate: $quote->getDueDate(),
            subtotal: $quote->getSubtotal(),
            discountType: $quote->getDiscountType()->value,
            discountValue: $quote->getDiscountValue(),
            shippingValue: $quote->getShippingValue(),
            totalAmount: $quote->getTotalAmount(),
            description: $quote->getDescription(),
            notes: $quote->getNotes(),
            internalNotes: $quote->getInternalNotes(),
            items: $items
        );
    }
}
