<?php

namespace App\Controller;

use DomainException;
use App\Entity\User;
use App\Exception\ValidationException;
use Psr\Log\LoggerInterface;
use App\Service\CompanyService;
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
        private readonly LoggerInterface $logger,
        private readonly CompanyService $companyService,

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
    public function save(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        try {
            $data = $request->request->all();
            $logoFile = $request->files->get('logo');

            $companyOutputDto = $this->companyService->handleUpsert($user, $data, $logoFile);

            return $this->json([
                'message' => 'COMPANY_SAVED_SUCCESSFULLY',
                'company' => $companyOutputDto
            ]);
        } catch (ValidationException $e) {
            return $this->json([
                'message' => 'INVALID_DATA',
                'errors' => $e->getErrors()
            ], 400);
        } catch (\Exception $e) {
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
