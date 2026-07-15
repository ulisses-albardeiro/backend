<?php

namespace App\Entity\Subscription;

use App\Entity\Company;
use App\Enum\Subscription\InvoiceStatus;
use App\Enum\Subscription\SubscriptionBillingType;
use App\Repository\Subscription\InvoiceRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\HasLifecycleCallbacks]
#[ORM\Entity(repositoryClass: InvoiceRepository::class)]
class Invoice
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Subscription $subscription = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Company $company = null;

    #[ORM\Column(length: 255, unique: true)]
    private ?string $asaasPaymentId = null;

    #[ORM\Column(enumType: InvoiceStatus::class)]
    private ?InvoiceStatus $status = null;

    #[ORM\Column(enumType: SubscriptionBillingType::class)]
    private ?SubscriptionBillingType $billingType = null;

    #[ORM\Column]
    private ?int $valueCents = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    private ?\DateTimeImmutable $dueDate = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $paidAt = null;

    #[ORM\Column(length: 500, nullable: true)]
    private ?string $invoiceUrl = null;

    #[ORM\Column(nullable: true)]
    private ?array $rawPayload = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\PrePersist]
    public function setCreatedAtValue(): void
    {
        $this->createdAt = new \DateTimeImmutable('now', new \DateTimeZone('America/Sao_Paulo'));
    }

    #[ORM\PreUpdate]
    public function setUpdatedAtValue(): void
    {
        $this->updatedAt = new \DateTimeImmutable('now', new \DateTimeZone('America/Sao_Paulo'));
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSubscription(): ?Subscription
    {
        return $this->subscription;
    }

    public function setSubscription(?Subscription $subscription): static
    {
        $this->subscription = $subscription;

        return $this;
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

    public function getAsaasPaymentId(): ?string
    {
        return $this->asaasPaymentId;
    }

    public function setAsaasPaymentId(string $asaasPaymentId): static
    {
        $this->asaasPaymentId = $asaasPaymentId;

        return $this;
    }

    public function getStatus(): ?InvoiceStatus
    {
        return $this->status;
    }

    public function setStatus(InvoiceStatus $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getBillingType(): ?SubscriptionBillingType
    {
        return $this->billingType;
    }

    public function setBillingType(SubscriptionBillingType $billingType): static
    {
        $this->billingType = $billingType;

        return $this;
    }

    public function getValueCents(): ?int
    {
        return $this->valueCents;
    }

    public function setValueCents(int $valueCents): static
    {
        $this->valueCents = $valueCents;

        return $this;
    }

    public function getDueDate(): ?\DateTimeImmutable
    {
        return $this->dueDate;
    }

    public function setDueDate(\DateTimeImmutable $dueDate): static
    {
        $this->dueDate = $dueDate;

        return $this;
    }

    public function getPaidAt(): ?\DateTimeImmutable
    {
        return $this->paidAt;
    }

    public function setPaidAt(?\DateTimeImmutable $paidAt): static
    {
        $this->paidAt = $paidAt;

        return $this;
    }

    public function getInvoiceUrl(): ?string
    {
        return $this->invoiceUrl;
    }

    public function setInvoiceUrl(?string $invoiceUrl): static
    {
        $this->invoiceUrl = $invoiceUrl;

        return $this;
    }

    public function getRawPayload(): ?array
    {
        return $this->rawPayload;
    }

    public function setRawPayload(?array $rawPayload): static
    {
        $this->rawPayload = $rawPayload;

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
}
