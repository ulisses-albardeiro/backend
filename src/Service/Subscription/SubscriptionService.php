<?php

namespace App\Service\Subscription;

use App\Entity\Company;
use App\Entity\Subscription\Invoice;
use App\Entity\Subscription\Plan;
use App\Entity\Subscription\Subscription;
use App\Enum\Subscription\InvoiceStatus;
use App\Enum\Subscription\PlanBillingCycle;
use App\Enum\Subscription\SubscriptionBillingType;
use App\Enum\Subscription\SubscriptionStatus;
use App\Mapper\Subscription\InvoiceMapper;
use App\Mapper\Subscription\SubscriptionMapper;
use App\DTO\Request\Subscription\ChoosePlanInputDTO;
use App\DTO\Response\Subscription\InvoiceOutputDTO;
use App\DTO\Response\Subscription\SubscriptionOutputDTO;
use App\Repository\Subscription\InvoiceRepository;
use App\Repository\Subscription\PlanRepository;
use App\Repository\Subscription\SubscriptionRepository;
use App\Service\Gateway\AsaasClient;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;

class SubscriptionService
{
    public function __construct(
        private PlanService $planService,
        private PlanRepository $planRepository,
        private SubscriptionRepository $subscriptionRepository,
        private InvoiceRepository $invoiceRepository,
        private SubscriptionMapper $mapper,
        private InvoiceMapper $invoiceMapper,
        private AsaasClient $asaasClient,
        private EntityManagerInterface $entityManager,
        private LoggerInterface $logger,
    ) {}

    public function startTrial(Company $company): Subscription
    {
        $defaultPlan = $this->planService->getDefaultActive();

        $trialDays = $defaultPlan?->getTrialDays() ?? 3;

        $subscription = new Subscription();
        $subscription->setCompany($company);
        $subscription->setStatus(SubscriptionStatus::TRIALING);
        $subscription->setBillingType(SubscriptionBillingType::UNDEFINED);
        $subscription->setTrialEndsAt((new \DateTimeImmutable('now', new \DateTimeZone('America/Sao_Paulo')))->modify("+{$trialDays} days"));

        $this->entityManager->persist($subscription);
        $this->entityManager->flush();

        return $subscription;
    }

    public function getByCompany(Company $company): ?SubscriptionOutputDTO
    {
        $subscription = $this->subscriptionRepository->findByCompany($company);

        return $subscription ? $this->mapper->toOutputDTO($subscription) : null;
    }

    public function choosePlan(Company $company, ChoosePlanInputDTO $dto): SubscriptionOutputDTO
    {
        $plan = $this->planRepository->find($dto->planId);

        if (!$plan || !$plan->isActive()) {
            throw new NotFoundHttpException('PLAN_NOT_FOUND');
        }

        $subscription = $this->subscriptionRepository->findByCompany($company);

        if (!$subscription) {
            throw new NotFoundHttpException('SUBSCRIPTION_NOT_FOUND');
        }

        if (!$subscription->canChangePlan()) {
            throw new BadRequestHttpException('PLAN_CHANGE_NOT_ALLOWED');
        }

        // A empresa pode não ter CNPJ (MEI/autônomo) — nesse caso o CPF/CNPJ do
        // responsável é coletado na própria tela de pagamento, não no cadastro da empresa.
        $document = $this->resolveDocument($company, $subscription, $dto);

        $billingType = SubscriptionBillingType::from($dto->billingType);

        if ($billingType === SubscriptionBillingType::CREDIT_CARD) {
            $this->assertCardDataPresent($dto);
        }

        $subscription->setDocumentNumber($document);

        $this->ensureAsaasCustomer($company, $subscription, $document);

        $creditCardToken = null;
        if ($billingType === SubscriptionBillingType::CREDIT_CARD) {
            $creditCardToken = $this->tokenizeCard($company, $subscription, $dto, $document);
        }

        $this->syncAsaasSubscription($subscription, $plan, $billingType, $creditCardToken);

        $subscription->setPlan($plan);
        $subscription->setBillingType($billingType);

        // Escolher um plano encerra o trial (ou qualquer estado anterior sem pagamento
        // confirmado) imediatamente — o acesso só volta quando o webhook do Asaas
        // confirmar o primeiro pagamento (status ACTIVE). Não mexe se já estava ACTIVE
        // (troca de plano de quem já paga não deve derrubar o acesso).
        if ($subscription->getStatus() !== SubscriptionStatus::ACTIVE) {
            $subscription->setStatus(SubscriptionStatus::INCOMPLETE);
            $subscription->setTrialEndsAt(new \DateTimeImmutable('now', new \DateTimeZone('America/Sao_Paulo')));
        }

        // O Asaas já gera a primeira cobrança (com QR Code Pix / link do boleto) de forma
        // síncrona ao criar a subscription — busca ela agora em vez de esperar o webhook,
        // que só chega se tivermos uma URL pública configurada (ngrok em dev, por exemplo).
        $this->reconcile($subscription);

        $this->entityManager->flush();

        return $this->mapper->toOutputDTO($subscription);
    }

    public function cancel(Company $company): void
    {
        $subscription = $this->subscriptionRepository->findByCompany($company);

        if (!$subscription) {
            throw new NotFoundHttpException('SUBSCRIPTION_NOT_FOUND');
        }

        // Cancela no Asaas qualquer cobrança já gerada e ainda não paga — senão ela
        // continua pagável por lá mesmo após o cancelamento, e o pagamento não teria
        // como ser reconciliado localmente (o vínculo com a subscription é zerado abaixo).
        foreach ($this->invoiceRepository->findPendingBySubscription($subscription) as $invoice) {
            try {
                $this->asaasClient->cancelPayment($invoice->getAsaasPaymentId());
                $invoice->setStatus(InvoiceStatus::CANCELED);
            } catch (HttpException $e) {
                $this->logger->warning('Falha ao cancelar cobrança pendente no Asaas.', [
                    'invoice_id' => $invoice->getId(),
                    'asaas_payment_id' => $invoice->getAsaasPaymentId(),
                    'error' => $e->getMessage(),
                ]);
            }
        }

        if ($subscription->getAsaasSubscriptionId()) {
            $this->asaasClient->cancelSubscription($subscription->getAsaasSubscriptionId());
        }

        // Limpa os dados da assinatura cancelada no Asaas — se a empresa assinar de novo
        // depois, precisa criar uma subscription nova lá, não tentar atualizar uma morta.
        $subscription->setAsaasSubscriptionId(null);
        $subscription->setCreditCardToken(null);
        $subscription->setCardLastFour(null);
        $subscription->setCardBrand(null);

        $subscription->setStatus(SubscriptionStatus::CANCELED);
        $subscription->setCanceledAt(new \DateTimeImmutable('now', new \DateTimeZone('America/Sao_Paulo')));

        $this->entityManager->flush();
    }

    /**
     * @return InvoiceOutputDTO[]
     */
    public function listInvoicesByCompany(Company $company): array
    {
        $invoices = $this->invoiceRepository->findByCompany($company);

        return array_map(fn(Invoice $invoice) => $this->invoiceMapper->toOutputDTO($invoice), $invoices);
    }

    /**
     * Busca o QR Code Pix (imagem + copia-e-cola) direto do Asaas, sem persistir —
     * usado pra pagar sem sair do nosso sistema (a página "fatura" hospedada pelo
     * Asaas traz propaganda de um produto que concorre com o nosso).
     */
    public function getPixQrCode(Company $company, int $invoiceId): array
    {
        $invoice = $this->invoiceRepository->findByIdAndCompany($invoiceId, $company);

        if (!$invoice) {
            throw new NotFoundHttpException('INVOICE_NOT_FOUND');
        }

        if ($invoice->getBillingType() !== SubscriptionBillingType::PIX) {
            throw new BadRequestHttpException('INVOICE_NOT_PIX');
        }

        return $this->asaasClient->getPixQrCode($invoice->getAsaasPaymentId());
    }

    public function syncFromPaymentWebhook(array $payload): void
    {
        $event = $payload['event'] ?? null;
        $payment = $payload['payment'] ?? null;

        if (!$event || !$payment || empty($payment['id']) || empty($payment['subscription'])) {
            return;
        }

        $subscription = $this->subscriptionRepository->findOneBy(['asaasSubscriptionId' => $payment['subscription']]);

        if (!$subscription) {
            return;
        }

        $this->applyPayment($subscription, $payment, $event === 'PAYMENT_DELETED');

        $this->entityManager->flush();
    }

    public function reconcile(Subscription $subscription): void
    {
        if (!$subscription->getAsaasSubscriptionId()) {
            return;
        }

        $payments = $this->asaasClient->listPaymentsBySubscription($subscription->getAsaasSubscriptionId());

        foreach ($payments['data'] ?? [] as $payment) {
            $this->applyPayment($subscription, $payment);
        }

        $this->entityManager->flush();
    }

    private function applyPayment(Subscription $subscription, array $payment, bool $deleted = false): void
    {
        $invoice = $this->invoiceRepository->findOneByAsaasPaymentId($payment['id']) ?? new Invoice();

        $invoice->setSubscription($subscription);
        $invoice->setCompany($subscription->getCompany());
        $invoice->setAsaasPaymentId($payment['id']);
        $invoice->setBillingType($this->mapAsaasBillingType($payment['billingType'] ?? null) ?? $subscription->getBillingType());
        $invoice->setValueCents((int) round((float) ($payment['value'] ?? 0) * 100));
        $invoice->setDueDate(new \DateTimeImmutable($payment['dueDate'] ?? 'now', new \DateTimeZone('America/Sao_Paulo')));
        $invoice->setInvoiceUrl($payment['invoiceUrl'] ?? $invoice->getInvoiceUrl());
        $invoice->setRawPayload($payment);

        $status = $deleted ? InvoiceStatus::CANCELED : $this->mapAsaasStatus($payment['status'] ?? 'PENDING');
        $invoice->setStatus($status);

        if (in_array($status, [InvoiceStatus::CONFIRMED, InvoiceStatus::RECEIVED], true)) {
            $invoice->setPaidAt(new \DateTimeImmutable($payment['paymentDate'] ?? $payment['clientPaymentDate'] ?? 'now', new \DateTimeZone('America/Sao_Paulo')));
        }

        $this->entityManager->persist($invoice);

        if (in_array($status, [InvoiceStatus::CONFIRMED, InvoiceStatus::RECEIVED], true)) {
            $subscription->setStatus(SubscriptionStatus::ACTIVE);
            $subscription->setCurrentPeriodEnd($this->calculatePeriodEnd($subscription, $invoice));
        } elseif ($status === InvoiceStatus::OVERDUE) {
            $subscription->setStatus(SubscriptionStatus::PAST_DUE);
        } elseif (in_array($status, [InvoiceStatus::REFUNDED, InvoiceStatus::CHARGEBACK], true)) {
            // Dinheiro devolvido (estorno) ou contestado (chargeback) — bloqueia o acesso
            // reaproveitando PAST_DUE, sem cancelar a assinatura no Asaas (a disputa pode
            // ser revertida a favor da empresa, e a recorrência continuaria intacta).
            $subscription->setStatus(SubscriptionStatus::PAST_DUE);
        }
    }

    /**
     * Até quando esse pagamento cobre o serviço. Não usa `Invoice::dueDate` direto porque o
     * Asaas sempre recebe `nextDueDate: +1 dia` na primeira cobrança de qualquer plano (ver
     * syncAsaasSubscription — é o que permite pagar via Pix na hora, sem esperar o ciclo), o
     * que faria o primeiro `currentPeriodEnd` cair sempre "amanhã", mesmo em planos
     * trimestrais/anuais. Calculamos a partir do ciclo do plano + data do pagamento em vez
     * disso, pra refletir o período pago de verdade.
     */
    private function calculatePeriodEnd(Subscription $subscription, Invoice $invoice): \DateTimeImmutable
    {
        $base = $invoice->getPaidAt() ?? new \DateTimeImmutable('now', new \DateTimeZone('America/Sao_Paulo'));

        return match ($subscription->getPlan()?->getBillingCycle()) {
            PlanBillingCycle::MONTHLY => $base->modify('+1 month'),
            PlanBillingCycle::QUARTERLY => $base->modify('+3 months'),
            PlanBillingCycle::YEARLY => $base->modify('+1 year'),
            default => $invoice->getDueDate(),
        };
    }

    private function mapAsaasStatus(string $status): InvoiceStatus
    {
        return match($status) {
            'CONFIRMED' => InvoiceStatus::CONFIRMED,
            'RECEIVED', 'RECEIVED_IN_CASH' => InvoiceStatus::RECEIVED,
            'OVERDUE' => InvoiceStatus::OVERDUE,
            'REFUNDED', 'REFUND_REQUESTED' => InvoiceStatus::REFUNDED,
            'CHARGEBACK_REQUESTED', 'CHARGEBACK_DISPUTE', 'AWAITING_CHARGEBACK_REVERSAL' => InvoiceStatus::CHARGEBACK,
            default => InvoiceStatus::PENDING,
        };
    }

    private function mapAsaasBillingType(?string $billingType): ?SubscriptionBillingType
    {
        return match($billingType) {
            'CREDIT_CARD' => SubscriptionBillingType::CREDIT_CARD,
            'PIX' => SubscriptionBillingType::PIX,
            'BOLETO' => SubscriptionBillingType::BOLETO,
            default => null,
        };
    }

    private function assertCardDataPresent(ChoosePlanInputDTO $dto): void
    {
        if (!$dto->cardHolderName || !$dto->cardNumber || !$dto->cardExpiryMonth || !$dto->cardExpiryYear || !$dto->cardCcv
            || !$dto->holderPostalCode || !$dto->holderAddressNumber || !$dto->holderPhone) {
            throw new BadRequestHttpException('CARD_DATA_REQUIRED');
        }
    }

    private function resolveDocument(Company $company, Subscription $subscription, ChoosePlanInputDTO $dto): string
    {
        // O front agora sempre mostra o campo (pré-preenchido com o CNPJ da empresa,
        // se houver) e deixa o usuário editar — o que ele confirma na tela é que vale,
        // por isso o DTO tem prioridade sobre o dado cadastrado da empresa.
        $document = $this->normalizeDocument($dto->holderCpfCnpj)
            ?: $company->getRegistrationNumber()
            ?: $subscription->getDocumentNumber();

        if (!$document) {
            throw new BadRequestHttpException('DOCUMENT_REQUIRED');
        }

        if (!in_array(strlen($document), [11, 14], true)) {
            throw new BadRequestHttpException('INVALID_DOCUMENT');
        }

        return $document;
    }

    private function normalizeDocument(?string $document): ?string
    {
        if (!$document) {
            return null;
        }

        $digits = preg_replace('/\D/', '', $document);

        return $digits !== '' ? $digits : null;
    }

    private function ensureAsaasCustomer(Company $company, Subscription $subscription, string $document): void
    {
        if ($subscription->getAsaasCustomerId()) {
            return;
        }

        $customer = $this->asaasClient->createCustomer([
            'name' => $company->getName(),
            'cpfCnpj' => $document,
            'email' => $company->getEmail(),
            'mobilePhone' => $company->getPhone(),
        ]);

        $subscription->setAsaasCustomerId($customer['id']);
    }

    private function tokenizeCard(Company $company, Subscription $subscription, ChoosePlanInputDTO $dto, string $document): string
    {
        $tokenized = $this->asaasClient->tokenizeCreditCard([
            'customer' => $subscription->getAsaasCustomerId(),
            'creditCard' => [
                'holderName' => $dto->cardHolderName,
                'number' => $dto->cardNumber,
                'expiryMonth' => $dto->cardExpiryMonth,
                'expiryYear' => $dto->cardExpiryYear,
                'ccv' => $dto->cardCcv,
            ],
            'creditCardHolderInfo' => [
                'name' => $company->getName(),
                'email' => $company->getEmail(),
                'cpfCnpj' => $document,
                'postalCode' => $dto->holderPostalCode,
                'addressNumber' => $dto->holderAddressNumber,
                'phone' => $dto->holderPhone,
            ],
        ]);

        $subscription->setCreditCardToken($tokenized['creditCardToken']);
        $subscription->setCardLastFour(substr((string) ($tokenized['creditCardNumber'] ?? ''), -4) ?: null);
        $subscription->setCardBrand($tokenized['creditCardBrand'] ?? null);

        return $tokenized['creditCardToken'];
    }

    private function syncAsaasSubscription(Subscription $subscription, Plan $plan, SubscriptionBillingType $billingType, ?string $creditCardToken): void
    {
        $payload = [
            'customer' => $subscription->getAsaasCustomerId(),
            'billingType' => strtoupper($billingType->value),
            'value' => $plan->getPriceCents() / 100,
            'nextDueDate' => (new \DateTimeImmutable('now', new \DateTimeZone('America/Sao_Paulo')))->modify('+1 day')->format('Y-m-d'),
            'cycle' => strtoupper($plan->getBillingCycle()->value),
            'description' => $plan->getName(),
        ];

        if ($creditCardToken) {
            $payload['creditCardToken'] = $creditCardToken;
        }

        if ($subscription->getAsaasSubscriptionId()) {
            $result = $this->asaasClient->updateSubscription($subscription->getAsaasSubscriptionId(), $payload);
        } else {
            $result = $this->asaasClient->createSubscription($payload);
            $subscription->setAsaasSubscriptionId($result['id']);
        }
    }
}
