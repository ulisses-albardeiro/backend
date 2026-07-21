<?php

namespace App\Entity\Order;

use App\Repository\Order\WorkOrderItemImageRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: WorkOrderItemImageRepository::class)]
class WorkOrderItemImage
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'images')]
    #[ORM\JoinColumn(nullable: false)]
    private ?WorkOrderItem $workOrderItem = null;

    #[ORM\Column(nullable: true)]
    private ?bool $isMain = null;

    #[ORM\Column(nullable: true)]
    private ?int $sortOrder = null;

    #[ORM\Column(length: 255)]
    private ?string $path = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getWorkOrderItem(): ?WorkOrderItem
    {
        return $this->workOrderItem;
    }

    public function setWorkOrderItem(?WorkOrderItem $workOrderItem): static
    {
        $this->workOrderItem = $workOrderItem;

        return $this;
    }

    public function isMain(): ?bool
    {
        return $this->isMain;
    }

    public function setIsMain(?bool $isMain): static
    {
        $this->isMain = $isMain;

        return $this;
    }

    public function getSortOrder(): ?int
    {
        return $this->sortOrder;
    }

    public function setSortOrder(?int $sortOrder): static
    {
        $this->sortOrder = $sortOrder;

        return $this;
    }

    public function getPath(): ?string
    {
        return $this->path;
    }

    public function setPath(string $path): static
    {
        $this->path = $path;

        return $this;
    }
}
