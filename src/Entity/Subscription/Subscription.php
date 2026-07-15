<?php

namespace App\Entity\Subscription;

use App\Entity\Company;
use App\Enum\Subscription\SubscriptionBillingType;
use App\Enum\Subscription\SubscriptionStatus;
use App\Repository\Subscription\SubscriptionRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\HasLifecycleCallbacks]
#[ORM\Entity(repositoryClass: SubscriptionRepository::class)]
class Subscription
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?Company $company = null;

    #[ORM\ManyToOne]
    private ?Plan $plan = null;

    #[ORM\Column(enumType: SubscriptionStatus::class)]
    private ?SubscriptionStatus $status = null;

    #[ORM\Column(enumType: SubscriptionBillingType::class)]
    private ?SubscriptionBillingType $billingType = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $asaasCustomerId = null;

    #[ORM\Column(length: 255, nullable: true, unique: true)]
    private ?string $asaasSubscriptionId = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $creditCardToken = null;

    #[ORM\Column(length: 4, nullable: true)]
    private ?string $cardLastFour = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $cardBrand = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $trialEndsAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $currentPeriodEnd = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $canceledAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $documentNumber = null;

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

    public function getCompany(): ?Company
    {
        return $this->company;
    }

    public function setCompany(Company $company): static
    {
        $this->company = $company;

        return $this;
    }

    public function getPlan(): ?Plan
    {
        return $this->plan;
    }

    public function setPlan(?Plan $plan): static
    {
        $this->plan = $plan;

        return $this;
    }

    public function getStatus(): ?SubscriptionStatus
    {
        return $this->status;
    }

    public function setStatus(SubscriptionStatus $status): static
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Fonte única de verdade pra "essa empresa pode usar o sistema agora?" —
     * usado tanto pelo bloqueio de rota (SubscriptionAccessSubscriber) quanto
     * pelo campo `blocked` devolvido ao frontend (SubscriptionMapper).
     */
    public function isBlocked(): bool
    {
        if ($this->status === SubscriptionStatus::TRIALING) {
            return $this->trialEndsAt !== null && $this->trialEndsAt < new \DateTimeImmutable();
        }

        // Cancelar não derruba o acesso na hora — a empresa já pagou pelo período
        // atual, então continua liberada até ele acabar. Sem `currentPeriodEnd`
        // (nunca teve pagamento confirmado), não há período pago a honrar.
        if ($this->status === SubscriptionStatus::CANCELED) {
            return $this->currentPeriodEnd === null || $this->currentPeriodEnd < new \DateTimeImmutable();
        }

        return $this->status?->blocksAccess() ?? true;
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

    public function getAsaasCustomerId(): ?string
    {
        return $this->asaasCustomerId;
    }

    public function setAsaasCustomerId(?string $asaasCustomerId): static
    {
        $this->asaasCustomerId = $asaasCustomerId;

        return $this;
    }

    public function getAsaasSubscriptionId(): ?string
    {
        return $this->asaasSubscriptionId;
    }

    public function setAsaasSubscriptionId(?string $asaasSubscriptionId): static
    {
        $this->asaasSubscriptionId = $asaasSubscriptionId;

        return $this;
    }

    public function getCreditCardToken(): ?string
    {
        return $this->creditCardToken;
    }

    public function setCreditCardToken(?string $creditCardToken): static
    {
        $this->creditCardToken = $creditCardToken;

        return $this;
    }

    public function getCardLastFour(): ?string
    {
        return $this->cardLastFour;
    }

    public function setCardLastFour(?string $cardLastFour): static
    {
        $this->cardLastFour = $cardLastFour;

        return $this;
    }

    public function getCardBrand(): ?string
    {
        return $this->cardBrand;
    }

    public function setCardBrand(?string $cardBrand): static
    {
        $this->cardBrand = $cardBrand;

        return $this;
    }

    public function getTrialEndsAt(): ?\DateTimeImmutable
    {
        return $this->trialEndsAt;
    }

    public function setTrialEndsAt(?\DateTimeImmutable $trialEndsAt): static
    {
        $this->trialEndsAt = $trialEndsAt;

        return $this;
    }

    public function getCurrentPeriodEnd(): ?\DateTimeImmutable
    {
        return $this->currentPeriodEnd;
    }

    public function setCurrentPeriodEnd(?\DateTimeImmutable $currentPeriodEnd): static
    {
        $this->currentPeriodEnd = $currentPeriodEnd;

        return $this;
    }

    public function getCanceledAt(): ?\DateTimeImmutable
    {
        return $this->canceledAt;
    }

    public function setCanceledAt(?\DateTimeImmutable $canceledAt): static
    {
        $this->canceledAt = $canceledAt;

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

    public function getDocumentNumber(): ?string
    {
        return $this->documentNumber;
    }

    public function setDocumentNumber(?string $documentNumber): static
    {
        $this->documentNumber = $documentNumber;

        return $this;
    }
}
