<?php

namespace App\Controller\Webhook;

use Psr\Log\LoggerInterface;
use App\Service\Subscription\SubscriptionService;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/api/webhook', name: 'api_webhook_', format: 'json')]
final class AsaasWebhookController extends AbstractController
{
    public function __construct(
        private LoggerInterface $logger,
        private SubscriptionService $subscriptionService,
        private string $asaasWebhookToken,
    ) {}

    #[Route('/asaas', name: 'asaas', methods: ['POST'])]
    public function handle(Request $request): JsonResponse
    {
        $receivedToken = $request->headers->get('asaas-access-token');

        if (!$this->asaasWebhookToken || !$receivedToken || !hash_equals($this->asaasWebhookToken, $receivedToken)) {
            return $this->json(['message' => 'UNAUTHORIZED'], 401);
        }

        $payload = json_decode($request->getContent(), true) ?? [];

        try {
            $this->subscriptionService->syncFromPaymentWebhook($payload);
        } catch (\Exception $e) {
            $this->logger->error('Failed to process Asaas webhook.', [
                'event' => $payload['event'] ?? null,
                'error' => $e->getMessage(),
            ]);
            // Responde 200 mesmo em erro interno para evitar reentregas agressivas do Asaas;
            // o erro fica registrado no log para investigação manual.
        }

        return $this->json(['received' => true]);
    }
}
