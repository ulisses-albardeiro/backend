<?php

namespace App\Controller\Admin;

use App\Service\UserService;
use Psr\Log\LoggerInterface;
use App\Service\CompanyService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('api/admin', name: 'admin_')]
#[IsGranted('IS_AUTHENTICATED_FULLY')]
final class UserAdminController extends AbstractController
{
    public function __construct(
        private LoggerInterface $logger,
    ) {}

    #[Route('/users', name: 'index')]
    #[IsGranted('ROLE_SUPER_ADMIN')]
    public function index(CompanyService $companyService): JsonResponse
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();

        return $this->json([]);
    }

    #[Route('/register', name: 'register', methods: ['POST'])]
    #[IsGranted('ROLE_SUPER_ADMIN')]
    public function register(Request $request, UserService $userService): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!$data || !isset($data['email'], $data['password'], $data['phone'], $data['name'])) {
            return $this->json(['error' => 'INVALID_INPUT_DATA'], 400);
        }

        try {
            $user = $userService->create($data);
            return $this->json(['message' => 'USER_CREATED_SUCCESS'], 201);
        } catch (\InvalidArgumentException $e) {
            return $this->json([
                'error' => $e->getMessage()
            ], 400);
        } catch (\Exception $e) {
            $this->logger->error('User registration error.', [
                'email' => $data['email'],
                'phone' => $data['phone'],
                'name' => $data['name'],
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return $this->json([
                'error' => 'INTERNAL_SERVER_ERROR'
            ], 500);
        }
    }
}
