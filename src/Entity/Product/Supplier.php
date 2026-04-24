<?php

namespace App\Entity\Product;

use App\Entity\Company;
use App\Enum\Product\PersonType;
use App\Enum\Product\ProductSupplierStatus;
use App\Repository\Product\SupplierRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SupplierRepository::class)]
class Supplier
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'suppliers')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Company $company = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $document = null;

    #[ORM\Column(enumType: PersonType::class)]
    private ?PersonType $personType = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $email = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $phone = null;

    #[ORM\Column(enumType: ProductSupplierStatus::class)]
    private ?ProductSupplierStatus $status = null;

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

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getDocument(): ?string
    {
        return $this->document;
    }

    public function setDocument(?string $document): static
    {
        $this->document = $document;

        return $this;
    }

    public function getPersonType(): ?PersonType
    {
        return $this->personType;
    }

    public function setPersonType(PersonType $personType): static
    {
        $this->personType = $personType;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): static
    {
        $this->phone = $phone;

        return $this;
    }

    public function getStatus(): ?ProductSupplierStatus
    {
        return $this->status;
    }

    public function setStatus(ProductSupplierStatus $status): static
    {
        $this->status = $status;

        return $this;
    }
}
