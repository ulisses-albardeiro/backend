<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Service\Admin\CompanyAdminService;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('api/admin', name: 'api_', format: 'json')]
#[IsGranted('ROLE_SUPER_ADMIN')]
#[IsGranted('IS_AUTHENTICATED_FULLY')]
final class CompanyAdminController extends AbstractController
{
    public function __construct(
        private CompanyAdminService $companyAdminService,
    ) {}

    #[Route('/companies', name: 'companies_show', methods: ['GET'])]
    public function show(): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();
        $companies = $this->companyAdminService->getCompanies();

        return $this->json($companies);
    }
}
