<?php

namespace App\Controller;

use Google_Client;
use App\Entity\User;
use Psr\Log\LoggerInterface;
use App\Service\CompanyService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;

final class AuthController extends AbstractController
{
    public function __construct(
        private LoggerInterface $logger,
        private EntityManagerInterface $em,
        private JWTTokenManagerInterface $jwtManager
    ) {}

    #[Route('/api/auth/google', name: 'api_google_auth', methods: ['POST'])]
    public function googleAuth(Request $request, CompanyService $companyService): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $idToken = $data['token'] ?? null;

        if (!$idToken) {
            return $this->json(['error' => 'TOKEN_NOT_PROVIDED'], 400);
        }

        try {
            $clientId = $this->getParameter('google_client_id');
            $client = new Google_Client(['client_id' => $clientId]);

            $payload = $client->verifyIdToken($idToken);

            if (!$payload) {
                $this->logger->warning('Invalid Google token attempt.', ['token' => substr($idToken, 0, 10) . '...']);
                return $this->json(['error' => 'INVALID_GOOGLE_TOKEN'], 401);
            }

            $email = $payload['email'];
            $name = $payload['name'];
            $googleId = $payload['sub'];

            $userRepository = $this->em->getRepository(User::class);
            $user = $userRepository->findOneBy(['email' => $email]);

            if (!$user) {
                $user = new User();
                $user->setEmail($email);
                $user->setName($name);

                if (method_exists($user, 'setGoogleId')) {
                    $user->setGoogleId($googleId);
                }

                $user->setPassword(bin2hex(random_bytes(16)));
                $user->setRoles(['ROLE_USER']);

                $this->em->persist($user);
                $this->em->flush();

                $this->logger->info('New user registered via Google OAuth.', ['email' => $email]);
            }

            $token = $this->jwtManager->create($user);

            return $this->json([
                'token' => $token,
                'user' => [
                    'id'    => $user->getId(),
                    'email' => $user->getEmail(),
                    'name'  => $user->getName(),
                    'company' => $companyService->getCompanyByUser($user) ?? null,
                ]
            ]);
        } catch (\Exception $e) {
            $this->logger->error('Google Auth Error.', [
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return $this->json([
                'error' => 'INTERNAL_SERVER_ERROR',
                'details' => $e->getMessage()
            ], 500);
        }
    }
}
