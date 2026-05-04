<?php

namespace App\Entity\Labor;

use App\Entity\Company;
use App\Entity\Quote\QuoteItem;
use App\Enum\Labor\LaborStatus;
use App\Enum\Labor\LaborUnit;
use App\Repository\Labor\LaborRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\HasLifecycleCallbacks]
#[ORM\Entity(repositoryClass: LaborRepository::class)]
class Labor
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'labors')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Company $company = null;

    #[ORM\ManyToOne(inversedBy: 'labors')]
    #[ORM\JoinColumn(nullable: false)]
    private ?LaborCategory $category = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(nullable: true)]
    private ?int $salePrice = null;

    #[ORM\Column(enumType: LaborStatus::class)]
    private ?LaborStatus $status = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\Column(nullable: true, enumType: LaborUnit::class)]
    private ?LaborUnit $unit = null;

    /**
     * @var Collection<int, QuoteItem>
     */
    #[ORM\OneToMany(targetEntity: QuoteItem::class, mappedBy: 'labor')]
    private Collection $quoteItems;

    public function __construct()
    {
        $this->quoteItems = new ArrayCollection();
    }

    #[ORM\PrePersist]
    public function setCreatedAtValue(): void
    {
        if ($this->createdAt === null) {
            $this->createdAt = new \DateTimeImmutable('now', new \DateTimeZone('America/Sao_Paulo'));
        }
    }

    #[ORM\PreUpdate]
    public function setUpdatedAtValue(): void
    {
        if ($this->updatedAt === null) {
            $this->updatedAt = new \DateTimeImmutable('now', new \DateTimeZone('America/Sao_Paulo'));
        }
    }

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

    public function getCategory(): ?LaborCategory
    {
        return $this->category;
    }

    public function setCategory(?LaborCategory $category): static
    {
        $this->category = $category;

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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getSalePrice(): ?int
    {
        return $this->salePrice;
    }

    public function setSalePrice(?int $salePrice): static
    {
        $this->salePrice = $salePrice;

        return $this;
    }

    public function getStatus(): ?LaborStatus
    {
        return $this->status;
    }

    public function setStatus(LaborStatus $status): static
    {
        $this->status = $status;

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

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getUnit(): ?LaborUnit
    {
        return $this->unit;
    }

    public function setUnit(?LaborUnit $unit): static
    {
        $this->unit = $unit;

        return $this;
    }

    /**
     * @return Collection<int, QuoteItem>
     */
    public function getQuoteItems(): Collection
    {
        return $this->quoteItems;
    }

    public function addQuoteItem(QuoteItem $quoteItem): static
    {
        if (!$this->quoteItems->contains($quoteItem)) {
            $this->quoteItems->add($quoteItem);
            $quoteItem->setLabor($this);
        }

        return $this;
    }

    public function removeQuoteItem(QuoteItem $quoteItem): static
    {
        if ($this->quoteItems->removeElement($quoteItem)) {
            // set the owning side to null (unless already changed)
            if ($quoteItem->getLabor() === $this) {
                $quoteItem->setLabor(null);
            }
        }

        return $this;
    }
}
