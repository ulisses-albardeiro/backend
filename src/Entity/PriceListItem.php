<?php

namespace App\Entity;

use App\Enum\UnitType;
use App\Repository\PriceListItemRepository;
use BcMath\Number;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PriceListItemRepository::class)]
class PriceListItem
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private ?Number $quantity = null;

    #[ORM\Column(enumType: UnitType::class)]
    private ?UnitType $unit = null;

    #[ORM\ManyToOne(inversedBy: 'priceListItems')]
    #[ORM\JoinColumn(nullable: false)]
    private ?PriceList $priceList = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getQuantity(): ?Number
    {
        return $this->quantity;
    }

    public function setQuantity(Number $quantity): static
    {
        $this->quantity = $quantity;

        return $this;
    }

    public function getUnit(): ?UnitType
    {
        return $this->unit;
    }

    public function setUnit(UnitType $unit): static
    {
        $this->unit = $unit;

        return $this;
    }

    public function getPriceList(): ?PriceList
    {
        return $this->priceList;
    }

    public function setPriceList(?PriceList $priceList): static
    {
        $this->priceList = $priceList;

        return $this;
    }
}
