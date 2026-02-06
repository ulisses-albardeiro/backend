<?php

namespace App\Controller;

use App\Service\PasswordResetRequestService;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/api/password-reset', 'api_password_reset_')]
final class PasswordResetController extends AbstractController
{
    public function __construct(
        private LoggerInterface $logger,
        private PasswordResetRequestService $resetService,
    ) {}

    #[Route('/request', name: 'request', methods: ['POST'])]
    public function request(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!$data || !isset($data['email']) || $data['email'] == '') {
            return $this->json(['error' => 'EMAIL_REQUIRED'], 400);
        }

        try {
            $resetRequest = $this->resetService->createRequest($data['email']);

            return $this->json(['message' => 'RESET_CODE_SENT_IF_EMAIL_EXISTS'], 200);
        } catch (\Exception $e) {
            $this->logger->error('Password reset failed.', [
                'email' => $data['email'],
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
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
            $this->logger->error('Password reset failed.', [
                'email' => $data['email'],
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return $this->json(['error' => 'INTERNAL_SERVER_ERROR'], 500);
        }
    }

    #[Route('/reset', name: 'confirm', methods: ['POST'])]
    public function reset(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $email = $data['email'] ?? null;
        $code = $data['code'] ?? null;
        $newPassword = $data['password'] ?? null;

        if (!$email || !$code || !$newPassword) {
            return $this->json(['error' => 'MISSING_DATA'], Response::HTTP_BAD_REQUEST);
        }

        $success = $this->resetService->resetPassword($email, $code, $newPassword);

        if (!$success) {
            return $this->json(['error' => 'FAILED_TO_RESET_PASSWORD'], Response::HTTP_UNAUTHORIZED);
        }

        return $this->json(['message' => 'PASSWORD_UPDATED_SUCCESS']);
    }
}
