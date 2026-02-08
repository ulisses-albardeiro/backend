<?php

namespace App\Controller;

use App\Entity\User;
use Psr\Log\LoggerInterface;
use App\Service\CompanyService;
use App\DTO\Request\CompanyInputDTO;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/api', name: 'api_', format: 'json')]
#[IsGranted('IS_AUTHENTICATED_FULLY')]
final class CompanyController extends AbstractController
{
    public function __construct(
        private LoggerInterface $logger,
        private CompanyService $companyService,

    ) {}

    #[Route('/company', name: 'company_get', methods: ['GET'])]
    public function show(): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();
        $companyDto = $this->companyService->getCompanyByUser($user);

        if (!$companyDto) {
            return $this->json(['message' => 'UNREGISTERED_COMPANY'], 404);
        }

        return $this->json(['company' => $companyDto]);
    }

    #[Route('/company', name: 'company_save', methods: ['POST', 'PUT'])]
    public function save(
        #[MapRequestPayload()] CompanyInputDTO $dto,
        UserInterface $user,
        Request $request
    ): JsonResponse {
        $logoFile = $request->files->get('logo');

        try {
            $output = $this->companyService->handleUpsert($user, $dto, $logoFile);
            return $this->json($output);
        } catch (\Exception $e) {
            /** @var User $user */
            $this->logger->error('Failed to save company.', [
                'user_id' => $user->getId(),
                'error' => $e->getMessage()
            ]);
            return $this->json([
                'message' => 'ERROR_SAVING_COMPANY'
            ], 500);
        }
    }
}
