<?php

namespace App\Entity;

use App\Enum\DiscountType;
use App\Enum\QuoteStatus;
use App\Repository\QuoteRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: QuoteRepository::class)]
class Quote
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $code = null;

    #[ORM\ManyToOne(inversedBy: 'quotes')]
    private ?Customer $customer = null;

    #[ORM\Column(type: 'string', enumType: QuoteStatus::class)]
    private QuoteStatus $status;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    private ?\DateTimeImmutable $date = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    private ?\DateTimeImmutable $due_date = null;

    #[ORM\Column]
    private ?int $subtotal = null;

    #[ORM\Column(enumType: DiscountType::class)]
    private ?DiscountType $discountType = null;

    #[ORM\Column(nullable: true)]
    private ?int $discountValue = null;

    #[ORM\Column(nullable: true)]
    private ?int $shippingValue = null;

    #[ORM\Column]
    private ?int $totalAmount = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $notes = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $internalNotes = null;

    /**
     * @var Collection<int, QuoteItem>
     */
    #[ORM\OneToMany(mappedBy: 'quote', targetEntity: QuoteItem::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $quoteItems;

    public function __construct()
    {
        $this->quoteItems = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): static
    {
        $this->code = $code;

        return $this;
    }

    public function getCustomer(): ?Customer
    {
        return $this->customer;
    }

    public function setCustomer(?Customer $customer): static
    {
        $this->customer = $customer;

        return $this;
    }

    /**
     * @return QuoteStatus
     */
    public function getStatus(): QuoteStatus
    {
        return $this->status;
    }

    public function setStatus(QuoteStatus $status): static
    {
        $this->status = $status;
        return $this;
    }

    public function getDate(): ?\DateTimeImmutable
    {
        return $this->date;
    }

    public function setDate(\DateTimeImmutable $date): static
    {
        $this->date = $date;

        return $this;
    }

    public function getDueDate(): ?\DateTimeImmutable
    {
        return $this->due_date;
    }

    public function setDueDate(\DateTimeImmutable $due_date): static
    {
        $this->due_date = $due_date;

        return $this;
    }

    public function getSubtotal(): ?int
    {
        return $this->subtotal;
    }

    public function setSubtotal(int $subtotal): static
    {
        $this->subtotal = $subtotal;

        return $this;
    }

    public function getDiscountType(): ?DiscountType
    {
        return $this->discountType;
    }

    public function setDiscountType(DiscountType $discountType): static
    {
        $this->discountType = $discountType;

        return $this;
    }

    public function getDiscountValue(): ?int
    {
        return $this->discountValue;
    }

    public function setDiscountValue(?int $discountValue): static
    {
        $this->discountValue = $discountValue;

        return $this;
    }

    public function getShippingValue(): ?int
    {
        return $this->shippingValue;
    }

    public function setShippingValue(?int $shippingValue): static
    {
        $this->shippingValue = $shippingValue;

        return $this;
    }

    public function getTotalAmount(): ?int
    {
        return $this->totalAmount;
    }

    public function setTotalAmount(int $totalAmount): static
    {
        $this->totalAmount = $totalAmount;

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

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function setNotes(?string $notes): static
    {
        $this->notes = $notes;

        return $this;
    }

    public function getInternalNotes(): ?string
    {
        return $this->internalNotes;
    }

    public function setInternalNotes(?string $internalNotes): static
    {
        $this->internalNotes = $internalNotes;

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
            $quoteItem->setQuote($this);
        }

        return $this;
    }

    public function removeQuoteItem(QuoteItem $quoteItem): static
    {
        if ($this->quoteItems->removeElement($quoteItem)) {
            // set the owning side to null (unless already changed)
            if ($quoteItem->getQuote() === $this) {
                $quoteItem->setQuote(null);
            }
        }

        return $this;
    }

    public function recalculateTotals(): void
    {
        $subtotal = 0;
        foreach ($this->quoteItems as $item) {
            $item->calculateTotal();
            $subtotal += $item->getTotalPrice();
        }
        $this->subtotal = $subtotal;

        $discount = $this->discountValue ?? 0;

        if ($this->discountType === DiscountType::PERCENTAGE) {
            $value = $this->discountValue ?? 0;
            $discount = (int) round($subtotal * ($value / 100));
        }

        $this->totalAmount = $subtotal - $discount + ($this->shippingValue ?? 0);
    }
}
