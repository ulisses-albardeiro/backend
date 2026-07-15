<?php

namespace App\Controller;

use App\DTO\Request\User\UserInputDTO;
use App\Service\UserService;
use Psr\Log\LoggerInterface;
use App\Repository\UserRepository;
use App\Service\CompanyService;
use App\Service\Subscription\SubscriptionService;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;

final class UserController extends AbstractController
{
    public function __construct(
        private LoggerInterface $logger,
        private UserService $userService,
        private UserRepository $userRepository,
        private JWTTokenManagerInterface $jwtManager,
    ) {}

    #[Route('api/me', name: 'app_me')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function index(CompanyService $companyService, SubscriptionService $subscriptionService): JsonResponse
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();

        $data = [
            'id'    => $user->getId(),
            'email' => $user->getUserIdentifier(),
            'name'  => $user->getName(),
            'roles' => $user->getRoles(),
            'company' => $companyService->getCompanyByUser($user) ?? null,
            'subscription' => $user->getCompany() ? $subscriptionService->getByCompany($user->getCompany()) : null,
        ];

        return $this->json($data);
    }

    #[Route('api/register', name: 'app_register', methods: ['POST'])]
    public function register(#[MapRequestPayload] UserInputDTO $dto): JsonResponse
    {
        try {
            $userOutput = $this->userService->create($dto);
            $user = $this->userRepository->find($userOutput->id);
            $token = $this->jwtManager->create($user);

            return $this->json([
                'message' => 'USER_CREATED_SUCCESS',
                'token' => $token,
                'user' => $userOutput,
            ], 201);
        } catch (\InvalidArgumentException $e) {
            return $this->json([
                'error' => $e->getMessage()
            ], 400);
        } catch (\Exception $e) {
            $this->logger->error('Erro de registro de usuário.', [
                'email' => $dto->email,
                'phone' => $dto->phone,
                'name' => $dto->name,
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return $this->json([
                'error' => 'INTERNAL_SERVER_ERROR'
            ], 500);
        }
    }
}
