<?php

namespace App\Entity\Product;

use App\Entity\Company;
use App\Enum\Product\InventoryMovementType;
use App\Repository\Product\InventoryMovementRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: InventoryMovementRepository::class)]
class InventoryMovement
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'inventoryMovements')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Company $company = null;

    #[ORM\ManyToOne(inversedBy: 'inventoryMovements')]
    private ?Product $product = null;

    #[ORM\Column(enumType: InventoryMovementType::class)]
    private ?InventoryMovementType $type = null;

    #[ORM\Column]
    private ?float $quantity = null;

    #[ORM\Column]
    private ?int $unitPrice = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCompany(): ?Company
    {
        return $this->company;
    }

    public function setCompany(?Company $company): static
    {
        $this->company = $company;

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

    public function getType(): ?InventoryMovementType
    {
        return $this->type;
    }

    public function setType(InventoryMovementType $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getQuantity(): ?float
    {
        return $this->quantity;
    }

    public function setQuantity(float $quantity): static
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }
}
