<?php

namespace App\Service\Gateway;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;

class AsaasClient
{
    public function __construct(
        private string $apiUrl,
        private string $apiKey,
        private HttpClientInterface $httpClient,
    ) {}

    public function createCustomer(array $data): array
    {
        return $this->request('POST', '/customers', $data);
    }

    public function updateCustomer(string $asaasCustomerId, array $data): array
    {
        return $this->request('PUT', "/customers/{$asaasCustomerId}", $data);
    }

    public function tokenizeCreditCard(array $data): array
    {
        return $this->request('POST', '/creditCard/tokenize', $data);
    }

    public function createSubscription(array $data): array
    {
        return $this->request('POST', '/subscriptions', $data);
    }

    public function updateSubscription(string $asaasSubscriptionId, array $data): array
    {
        return $this->request('PUT', "/subscriptions/{$asaasSubscriptionId}", $data);
    }

    public function cancelSubscription(string $asaasSubscriptionId): void
    {
        $this->request('DELETE', "/subscriptions/{$asaasSubscriptionId}");
    }

    public function getPayment(string $asaasPaymentId): array
    {
        return $this->request('GET', "/payments/{$asaasPaymentId}");
    }

    public function listPaymentsBySubscription(string $asaasSubscriptionId): array
    {
        return $this->request('GET', "/subscriptions/{$asaasSubscriptionId}/payments");
    }

    public function getPixQrCode(string $asaasPaymentId): array
    {
        return $this->request('GET', "/payments/{$asaasPaymentId}/pixQrCode");
    }

    private function request(string $method, string $path, ?array $body = null): array
    {
        try {
            $response = $this->httpClient->request($method, $this->apiUrl . $path, [
                'headers' => [
                    'access_token' => $this->apiKey,
                    'Content-Type' => 'application/json',
                    'User-Agent' => 'MeusOrcamentos',
                ],
                'json' => $body,
            ]);

            $content = $response->getContent(false);
            $decoded = $content !== '' ? json_decode($content, true) : [];

            if ($response->getStatusCode() >= 400) {
                $message = $decoded['errors'][0]['description'] ?? 'Erro ao comunicar com o Asaas.';
                throw new HttpException($response->getStatusCode(), $message);
            }

            return $decoded ?? [];
        } catch (ExceptionInterface $e) {
            throw new HttpException(502, 'Falha ao comunicar com o Asaas: ' . $e->getMessage());
        }
    }
}
