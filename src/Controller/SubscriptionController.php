<?php

namespace App\Controller;

use App\Entity\User;
use Psr\Log\LoggerInterface;
use App\Service\Subscription\SubscriptionService;
use App\DTO\Request\Subscription\ChoosePlanInputDTO;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

#[Route('/api/subscription', name: 'api_subscription_', format: 'json')]
#[IsGranted('IS_AUTHENTICATED_FULLY')]
final class SubscriptionController extends AbstractController
{
    public function __construct(
        private LoggerInterface $logger,
        private SubscriptionService $subscriptionService,
    ) {}

    #[Route('', name: 'show', methods: ['GET'])]
    public function show(): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();
        $subscription = $this->subscriptionService->getByCompany($user->getCompany());

        if (!$subscription) {
            return $this->json(['message' => 'SUBSCRIPTION_NOT_FOUND'], 404);
        }

        return $this->json(['subscription' => $subscription]);
    }

    #[Route('', name: 'choose_plan', methods: ['POST'])]
    public function choosePlan(#[MapRequestPayload] ChoosePlanInputDTO $dto): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        try {
            $subscription = $this->subscriptionService->choosePlan($user->getCompany(), $dto);
            return $this->json(['subscription' => $subscription]);
        } catch (NotFoundHttpException $e) {
            return $this->json(['message' => $e->getMessage()], 404);
        } catch (BadRequestHttpException $e) {
            return $this->json(['message' => $e->getMessage()], 400);
        } catch (HttpException $e) {
            $this->logger->error('Failed to choose plan.', [
                'user_id' => $user->getId(),
                'error' => $e->getMessage(),
            ]);
            // $e->getMessage() aqui é a descrição de erro que o próprio Asaas devolveu
            // (ex: "O CPF/CNPJ informado é inválido.") — segura o suficiente pra mostrar
            // direto pro usuário, é sempre sobre os dados de pagamento, nunca detalhe interno.
            return $this->json(['message' => 'ASAAS_ERROR', 'detail' => $e->getMessage()], 502);
        }
    }

    #[Route('/invoices', name: 'invoices', methods: ['GET'])]
    public function invoices(): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        return $this->json(['invoices' => $this->subscriptionService->listInvoicesByCompany($user->getCompany())]);
    }

    #[Route('/invoices/{id}/pix-qrcode', name: 'invoice_pix_qrcode', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function pixQrCode(int $id): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        try {
            $qrCode = $this->subscriptionService->getPixQrCode($user->getCompany(), $id);
            return $this->json($qrCode);
        } catch (NotFoundHttpException $e) {
            return $this->json(['message' => $e->getMessage()], 404);
        } catch (BadRequestHttpException $e) {
            return $this->json(['message' => $e->getMessage()], 400);
        } catch (HttpException $e) {
            $this->logger->error('Failed to fetch Pix QR code.', [
                'user_id' => $user->getId(),
                'invoice_id' => $id,
                'error' => $e->getMessage(),
            ]);
            return $this->json(['message' => 'ASAAS_ERROR', 'detail' => $e->getMessage()], 502);
        }
    }

    #[Route('/cancel', name: 'cancel', methods: ['POST'])]
    public function cancel(): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        try {
            $this->subscriptionService->cancel($user->getCompany());
            return $this->json(null, 204);
        } catch (NotFoundHttpException $e) {
            return $this->json(['message' => $e->getMessage()], 404);
        } catch (HttpException $e) {
            $this->logger->error('Failed to cancel subscription.', [
                'user_id' => $user->getId(),
                'error' => $e->getMessage(),
            ]);
            return $this->json(['message' => 'ASAAS_ERROR', 'detail' => $e->getMessage()], 502);
        }
    }
}
