<?php

namespace App\EventSubscriber;

use App\Entity\User;
use App\Repository\Subscription\SubscriptionRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class SubscriptionAccessSubscriber implements EventSubscriberInterface
{
    /**
     * Rotas sob /api que continuam acessíveis mesmo com a assinatura bloqueada:
     * autenticação/onboarding, dados da própria empresa (para corrigir CNPJ antes de
     * escolher um plano), a própria área de assinatura/faturas, o catálogo público
     * de planos, o webhook do Asaas e o painel admin (não é tenant).
     */
    private const ALLOWED_PATH_PREFIXES = [
        '/api/auth',
        '/api/register',
        '/api/login_check',
        '/api/admin/login_check',
        '/api/password-reset',
        '/api/plans',
        '/api/webhook',
        '/api/me',
        '/api/subscription',
        '/api/company',
        '/api/admin',
    ];

    public function __construct(
        private Security $security,
        private SubscriptionRepository $subscriptionRepository,
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::CONTROLLER => 'onKernelController',
        ];
    }

    public function onKernelController(ControllerEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $path = $event->getRequest()->getPathInfo();

        if (!str_starts_with($path, '/api') || $this->isAllowedPath($path)) {
            return;
        }

        /** @var User|null $user */
        $user = $this->security->getUser();

        if (!$user || !$user->getCompany()) {
            return;
        }

        $subscription = $this->subscriptionRepository->findByCompany($user->getCompany());

        if (!$subscription || !$subscription->isBlocked()) {
            return;
        }

        $event->setController(fn() => new JsonResponse(['error' => 'SUBSCRIPTION_REQUIRED'], 402));
    }

    private function isAllowedPath(string $path): bool
    {
        foreach (self::ALLOWED_PATH_PREFIXES as $prefix) {
            if (str_starts_with($path, $prefix)) {
                return true;
            }
        }

        return false;
    }
}
