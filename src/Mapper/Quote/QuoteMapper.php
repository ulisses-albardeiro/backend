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
use App\Entity\Customer\CustomerAsset;
use App\Repository\CustomerRepository;
use App\Service\QuoteItemImageService;
use Doctrine\ORM\EntityManagerInterface;

class QuoteMapper
{
    public function __construct(
        private CustomerRepository $customerRepository,
        private EntityManagerInterface $em,
        private QuoteItemImageService $quoteItemImageService
    ) {}

    /**
     * @param array<int, array{images?: \Symfony\Component\HttpFoundation\File\UploadedFile[]}> $itemImageFiles Indexado pela mesma posição de $dto->items
     */
    public function toEntity(QuoteInputDTO $dto, Company $company, ?CustomerAsset $asset, ?Quote $quote = null, array $itemImageFiles = []): Quote
    {
        $quote = $quote ?? new Quote();

        $customer = $this->customerRepository->findOneBy([
            'id' => $dto->customerId,
            'company' => $company
        ]);
        
        $quote->setCompany($company);
        $quote->setCustomer($customer);
        $quote->setAsset($asset);
        $quote->setDate($dto->date);
        $quote->setDueDate($dto->dueDate);
        $quote->setDescription($dto->description);
        $quote->setNotes($dto->notes);
        $quote->setStatus($dto->status);
        
        $quote->setDiscountType(DiscountType::from($dto->discountType));
        $quote->setDiscountValue($dto->discountValue);
        $quote->setShippingValue($dto->shippingValue);
        $quote->setInternalNotes($dto->internalNotes);
        $quote->setIncludeSignature($dto->includeSignature);

        // Indexa os itens já persistidos por id, para casar com o DTO em vez de recriar tudo
        // (recriar destruiria as imagens anexadas a cada edição do orçamento, via orphanRemoval)
        $existingItemsById = [];
        foreach ($quote->getQuoteItems() as $existingItem) {
            if ($existingItem->getId()) {
                $existingItemsById[$existingItem->getId()] = $existingItem;
            }
        }

        $keptItemIds = [];

        foreach ($dto->items as $index => $itemDto) {
            if ($itemDto->id !== null && isset($existingItemsById[$itemDto->id])) {
                $item = $existingItemsById[$itemDto->id];
                $keptItemIds[] = $itemDto->id;
            } else {
                $item = new QuoteItem();
                $quote->addQuoteItem($item);
            }

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

            if (!empty($itemImageFiles[$index]['images'])) {
                $this->quoteItemImageService->addImages($item, $company, $itemImageFiles[$index]['images']);
            }
        }

        // Remove apenas os itens que o usuário de fato excluiu da lista (não vieram no DTO)
        foreach ($existingItemsById as $id => $existingItem) {
            if (!in_array($id, $keptItemIds, true)) {
                $quote->removeQuoteItem($existingItem);
            }
        }

        return $quote;
    }

    public function toOutputDTO(Quote $quote): QuoteOutputDTO
    {
        $company = $quote->getCompany();

        $items = array_map(function (QuoteItem $item) use ($company) {
            return new QuoteItemOutputDTO(
                id: $item->getId(),
                description: $item->getDescription(),
                quantity: $item->getQuantity(),
                unitPrice: $item->getUnitPrice(),
                totalPrice: $item->getTotalPrice(),
                laborId: $item->getLabor()?->getId(),
                laborName: $item->getLabor()?->getName(),
                laborUnit: $item->getLabor()?->getUnit()->value,
                productUnit: $item->getProduct()?->getUnit()->value,
                productId: $item->getProduct()?->getId(),
                productName: $item->getProduct()?->getName(),
                images: $this->quoteItemImageService->formatImages($item, $company),
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
            assetId: $quote->getAsset()?->getId(),
            assetName: $quote->getAsset()?->getName(),
            includeSignature: $quote->isIncludeSignature(),
            items: $items
        );
    }
}
