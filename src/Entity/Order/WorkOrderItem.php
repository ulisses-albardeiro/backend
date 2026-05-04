<?php

namespace App\Entity\Order;

use App\Entity\Labor\Labor;
use App\Entity\Product\Product;
use App\Repository\Order\WorkOrderItemRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: WorkOrderItemRepository::class)]
class WorkOrderItem
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'workOrderItems')]
    #[ORM\JoinColumn(nullable: false)]
    private ?WorkOrder $workOrder = null;

    #[ORM\ManyToOne(inversedBy: 'workOrderItems')]
    private ?Product $product = null;

    #[ORM\ManyToOne(inversedBy: 'workOrderItems')]
    private ?Labor $labor = null;

    #[ORM\Column(length: 255)]
    private ?string $description = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private ?string $quantity = null;

    #[ORM\Column]
    private ?int $unitPrice = null;

    #[ORM\Column]
    private ?int $totalPrice = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getWorkOrder(): ?WorkOrder
    {
        return $this->workOrder;
    }

    public function setWorkOrder(?WorkOrder $workOrder): static
    {
        $this->workOrder = $workOrder;

        return $this;
    }

    public function getProduct(): ?Product
    {
        return $this->product;
    }

    public function setProduct(?Product $product): static
    {
        $this->product = $product;

        return $this;
    }

    public function getLabor(): ?Labor
    {
        return $this->labor;
    }

    public function setLabor(?Labor $labor): static
    {
        $this->labor = $labor;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getQuantity(): ?string
    {
        return $this->quantity;
    }

    public function setQuantity(string $quantity): static
    {
        $this->quantity = $quantity;

        return $this;
    }

    public function getUnitPrice(): ?int
    {
        return $this->unitPrice;
    }

    public function setUnitPrice(int $unitPrice): static
    {
        $this->unitPrice = $unitPrice;

        return $this;
    }

    public function getTotalPrice(): ?int
    {
        return $this->totalPrice;
    }

    public function setTotalPrice(int $totalPrice): static
    {
        $this->totalPrice = $totalPrice;

        return $this;
    }
}
