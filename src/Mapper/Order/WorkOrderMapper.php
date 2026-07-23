<?php

namespace App\Mapper\Order;

use App\Entity\Order\WorkOrder;
use App\Entity\Order\WorkOrderItem;
use App\Entity\Company;
use App\Entity\Product\Product;
use App\Entity\Labor\Labor;
use App\Entity\Quote\Quote;
use App\DTO\Request\Order\WorkOrderInputDTO;
use App\DTO\Response\Order\WorkOrderOutputDTO;
use App\DTO\Response\Order\WorkOrderItemOutputDTO;
use App\Entity\Customer\CustomerAsset;
use App\Entity\Quote\QuoteItem;
use App\Repository\CustomerRepository;
use App\Service\Order\WorkOrderItemImageService;
use Doctrine\ORM\EntityManagerInterface;

class WorkOrderMapper
{
    public function __construct(
        private CustomerRepository $customerRepository,
        private EntityManagerInterface $em,
        private WorkOrderItemImageService $workOrderItemImageService
    ) {}

    /**
     * @param array<int, array{images?: \Symfony\Component\HttpFoundation\File\UploadedFile[]}> $itemImageFiles Indexado pela mesma posição de $dto->items
     */
    public function toEntity(WorkOrderInputDTO $dto, Company $company, ?CustomerAsset $customerAsset, ?WorkOrder $workOrder = null, array $itemImageFiles = []): WorkOrder
    {
        $workOrder = $workOrder ?? new WorkOrder();

        $customer = $this->customerRepository->findOneBy([
            'id' => $dto->customerId,
            'company' => $company
        ]);

        $workOrder->setCompany($company);
        $workOrder->setCustomer($customer);

        $workOrder->setAsset($customerAsset);
        
        $workOrder->setTitle($dto->title);
        $workOrder->setStatus($dto->status);
        $workOrder->setProblemDescription($dto->problemDescription);
        $workOrder->setTechnicalReport($dto->technicalReport);
        $workOrder->setEquipment($dto->equipment);
        $workOrder->setStartDate($dto->startDate);
        $workOrder->setEndDate($dto->endDate);
        $workOrder->setIncludeSignature($dto->includeSignature);

        if (!$workOrder->getId()) {
            $year = (new \DateTimeImmutable('now', new \DateTimeZone('America/Sao_Paulo')))->format('dmY');
            $uniquePart = strtoupper(substr(uniqid(), -4));
            $code = sprintf('OS-%s-%s', $year, $uniquePart);
            $workOrder->setCode($code);
        }

        if ($workOrder->getCreatedAt() === null) {
            $workOrder->setCreatedAt(new \DateTimeImmutable('now', new \DateTimeZone('America/Sao_Paulo')));
        }
        $workOrder->setUpdatedAt(new \DateTimeImmutable('now', new \DateTimeZone('America/Sao_Paulo')));

        if ($dto->quoteId) {
            $workOrder->setQuote($this->em->getReference(Quote::class, $dto->quoteId));
        }

        // Indexa os itens já persistidos por id, para casar com o DTO em vez de recriar tudo
        // (recriar destruiria as imagens anexadas a cada edição da OS, via orphanRemoval) —
        // mesmo padrão de QuoteMapper::toEntity().
        $existingItemsById = [];
        foreach ($workOrder->getWorkOrderItems() as $existingItem) {
            if ($existingItem->getId()) {
                $existingItemsById[$existingItem->getId()] = $existingItem;
            }
        }

        $keptItemIds = [];
        $totalOS = 0;

        foreach ($dto->items as $index => $itemDto) {
            if ($itemDto->id !== null && isset($existingItemsById[$itemDto->id])) {
                $item = $existingItemsById[$itemDto->id];
                $keptItemIds[] = $itemDto->id;
            } else {
                $item = new WorkOrderItem();
                $workOrder->addWorkOrderItem($item);
            }

            $item->setDescription($itemDto->description);
            $item->setQuantity($itemDto->quantity);
            $item->setUnitPrice($itemDto->unitPrice);

            $itemTotal = (int) round($itemDto->unitPrice * (float) $itemDto->quantity);
            $item->setTotalPrice($itemTotal);

            $totalOS += $itemTotal;

            if ($itemDto->laborId > 0) {
                $item->setLabor($this->em->getReference(Labor::class, $itemDto->laborId));
            }

            if ($itemDto->productId > 0) {
                $item->setProduct($this->em->getReference(Product::class, $itemDto->productId));
            }

            if ($itemDto->sourceQuoteItemId) {
                $sourceItem = $this->em->find(QuoteItem::class, $itemDto->sourceQuoteItemId);
                if ($sourceItem && $sourceItem->getQuote()->getCompany()->getId() === $company->getId()) {
                    $this->workOrderItemImageService->copyFromQuoteItem($item, $sourceItem);
                }
            }

            if (!empty($itemImageFiles[$index]['images'])) {
                $this->workOrderItemImageService->addImages($item, $company, $itemImageFiles[$index]['images']);
            }
        }

        // Remove apenas os itens que o usuário de fato excluiu da lista (não vieram no DTO)
        foreach ($existingItemsById as $id => $existingItem) {
            if (!in_array($id, $keptItemIds, true)) {
                $workOrder->removeWorkOrderItem($existingItem);
            }
        }

        $workOrder->setTotalAmount($totalOS);

        return $workOrder;
    }

    public function toOutputDTO(WorkOrder $workOrder): WorkOrderOutputDTO
    {
        $company = $workOrder->getCompany();

        $items = array_map(function (WorkOrderItem $item) use ($company) {
            return new WorkOrderItemOutputDTO(
                id: $item->getId(),
                description: $item->getDescription(),
                quantity: $item->getQuantity(),
                unitPrice: $item->getUnitPrice(),
                totalPrice: $item->getTotalPrice(),
                productId: $item->getProduct()?->getId(),
                productName: $item->getProduct()?->getName(),
                productUnit: $item->getProduct()?->getUnit()->value,
                laborId: $item->getLabor()?->getId(),
                laborName: $item->getLabor()?->getName(),
                laborUnit: $item->getLabor()?->getUnit()->value,
                images: $this->workOrderItemImageService->formatImages($item, $company),
            );
        }, $workOrder->getWorkOrderItems()->toArray());

        return new WorkOrderOutputDTO(
            id: $workOrder->getId(),
            code: $workOrder->getCode(),
            title: $workOrder->getTitle(),
            companyId: $workOrder->getCompany()->getId(),
            customerId: $workOrder->getCustomer()->getId(),
            customerName: $workOrder->getCustomer()->getName(),
            quoteId: $workOrder->getQuote()?->getId(),
            status: $workOrder->getStatus()->value,
            statusLabel: $workOrder->getStatus()->getLabel(),
            problemDescription: $workOrder->getProblemDescription(),
            technicalReport: $workOrder->getTechnicalReport(),
            equipment: $workOrder->getEquipment(),
            assetId: $workOrder->getAsset()?->getId(),
            assetName: $workOrder->getAsset()?->getName(),
            startDate: $workOrder->getStartDate(),
            endDate: $workOrder->getEndDate(),
            includeSignature: $workOrder->isIncludeSignature(),
            createdAt: $workOrder->getCreatedAt(),
            updatedAt: $workOrder->getUpdatedAt(),
            totalAmount: $workOrder->getTotalAmount(),
            items: $items
        );
    }
}
