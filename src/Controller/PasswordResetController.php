<?php

namespace App\Controller;

use App\Service\PasswordResetRequestService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/api/password-reset', 'api_password_reset_')]
final class PasswordResetController extends AbstractController
{
    public function __construct(
        private PasswordResetRequestService $resetService
    ) {}

    #[Route('/request', name: 'request', methods: ['POST'])]
    public function request(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!$data || !isset($data['email'])) {
            return $this->json(['error' => 'EMAIL_REQUIRED'], 400);
        }

        try {
            $resetRequest = $this->resetService->createRequest($data['email']);

            return $this->json(['message' => 'RESET_CODE_SENT_IF_EMAIL_EXISTS'], 200);
        } catch (\Exception $e) {
            return $this->json(['error' => 'INTERNAL_SERVER_ERROR'], 500);
        }
    }

    #[Route('/validate', name: 'validate', methods: ['POST'])]
    public function validate(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!$data || !isset($data['email'], $data['code'])) {
            return $this->json(['error' => 'INVALID_INPUT_DATA'], 400);
        }

        try {
            $isValid = $this->resetService->validateCode($data['email'], $data['code']);

            if (!$isValid) {
                return $this->json(['error' => 'INVALID_OR_EXPIRED_CODE'], 401);
            }

            return $this->json(['message' => 'CODE_VALIDATED_SUCCESS'], 200);
        } catch (\Exception $e) {
            return $this->json(['error' => 'INTERNAL_SERVER_ERROR'], 500);
        }
    }
}
