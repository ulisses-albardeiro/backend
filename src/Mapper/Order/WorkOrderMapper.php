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
use App\Repository\CustomerRepository;
use Doctrine\ORM\EntityManagerInterface;

class WorkOrderMapper
{
    public function __construct(
        private CustomerRepository $customerRepository,
        private EntityManagerInterface $em
    ) {}

    public function toEntity(WorkOrderInputDTO $dto, Company $company, ?WorkOrder $workOrder = null): WorkOrder
    {
        $workOrder = $workOrder ?? new WorkOrder();

        $customer = $this->customerRepository->findOneBy([
            'id' => $dto->customerId,
            'company' => $company
        ]);

        $workOrder->setCompany($company);
        $workOrder->setCustomer($customer);
        
        $workOrder->setTitle($dto->title);
        $workOrder->setStatus($dto->status);
        $workOrder->setProblemDescription($dto->problemDescription);
        $workOrder->setTechnicalReport($dto->technicalReport);
        $workOrder->setEquipment($dto->equipment);
        $workOrder->setStartDate($dto->startDate);
        $workOrder->setEndDate($dto->endDate);
        
        if (!$workOrder->getId()) {
            $year = date('dmY');
            $uniquePart = strtoupper(substr(uniqid(), -4));
            $code = sprintf('OS-%s-%s', $year, $uniquePart);
            $workOrder->setCode($code);
        }

        if ($workOrder->getCreatedAt() === null) {
            $workOrder->setCreatedAt(new \DateTimeImmutable());
        }
        $workOrder->setUpdatedAt(new \DateTimeImmutable());

        if ($dto->quoteId) {
            $workOrder->setQuote($this->em->getReference(Quote::class, $dto->quoteId));
        }

        if ($workOrder->getId()) {
            foreach ($workOrder->getWorkOrderItems() as $oldItem) {
                $workOrder->removeWorkOrderItem($oldItem);
            }
        }

        $totalOS = 0;

        foreach ($dto->items as $itemDto) {
            $item = new WorkOrderItem();
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

            $workOrder->addWorkOrderItem($item);
        }

        $workOrder->setTotalAmount($totalOS);

        return $workOrder;
    }

    public function toOutputDTO(WorkOrder $workOrder): WorkOrderOutputDTO
    {
        $items = array_map(function (WorkOrderItem $item) {
            return new WorkOrderItemOutputDTO(
                id: $item->getId(),
                description: $item->getDescription(),
                quantity: $item->getQuantity(),
                unitPrice: $item->getUnitPrice(),
                totalPrice: $item->getTotalPrice(),
                productId: $item->getProduct()?->getId(),
                productName: $item->getProduct()?->getName(),
                laborId: $item->getLabor()?->getId(),
                laborName: $item->getLabor()?->getName(),
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
            startDate: $workOrder->getStartDate(),
            endDate: $workOrder->getEndDate(),
            createdAt: $workOrder->getCreatedAt(),
            updatedAt: $workOrder->getUpdatedAt(),
            totalAmount: $workOrder->getTotalAmount(),
            items: $items
        );
    }
}
