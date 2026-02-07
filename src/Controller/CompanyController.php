<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\CompanyService;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[IsGranted('IS_AUTHENTICATED_FULLY')]
#[Route('/api', name: 'api_')]
final class CompanyController extends AbstractController
{
    public function __construct(
        private LoggerInterface $logger,
    ) {}

    #[Route('/company', name: 'company_get', methods: ['GET'])]
    public function show(): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();
        $company = $user->getCompany();

        if (!$company) {
            return $this->json(['message' => 'UNREGISTERED_COMPANY'], 404);
        }

        return $this->json(['company' => $company], 200, [], ['groups' => ['company:read']]);
    }

    #[Route('/company', name: 'company_upsert', methods: ['POST'])]
    public function store(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();
        $data = json_decode($request->getContent(), true);

        if (!$data) {
            return $this->json(['message' => 'INVALID_JSON'], 400);
        }

        try {

            //Service...
            
            return $this->json([
                'message' => 'COMPANY_SAVED_SUCCESSFULLY',
                'company' => ''
            ], 200, [], ['groups' => ['company:read']]);
        } catch (\Exception $e) {
            $this->logger->error('Company saved failed.', [
                'user' => $user->getId(),
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return $this->json(['message' => 'ERROR_SAVING_COMPANY'], 500);
        }
    }

    #[Route('/company', name: 'company_upsert', methods: ['PUT'])]
    public function update(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();
        $data = json_decode($request->getContent(), true);

        if (!$data) {
            return $this->json(['message' => 'INVALID_JSON'], 400);
        }

        try {


            return $this->json([
                'message' => 'COMPANY_SAVED_SUCCESSFULLY',
                'company' => ''
            ], 200, [], ['groups' => ['company:read']]);
        } catch (\Exception $e) {
            $this->logger->error('Company saved failed.', [
                'user' => $user->getId(),
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return $this->json(['message' => 'ERROR_UPDATING_COMPANY'], 500);
        }
    }
}
