<?php

namespace App\Controller;

use App\Entity\User;
use Psr\Log\LoggerInterface;
use App\Service\CompanyService;
use App\DTO\Request\CompanyInputDTO;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[IsGranted('IS_AUTHENTICATED_FULLY')]
#[Route('/api', name: 'api_')]
final class CompanyController extends AbstractController
{
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly ValidatorInterface $validator,
        private readonly CompanyService $companyService,
        private readonly SerializerInterface $serializer,
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
            $companyInputDto = $this->serializer->deserialize(
                $request->getContent(),
                CompanyInputDTO::class,
                'json'
            );

            $errors = $this->validator->validate($companyInputDto);
            if (count($errors) > 0) {
                return $this->json(['errors' => $errors], 400);
            }

            $companyOutputDto = $this->companyService->upsertCompany($companyInputDto, $user);

            return $this->json([
                'message' => 'COMPANY_SAVED_SUCCESSFULLY',
                'company' => $companyOutputDto
            ]);

        } catch (\Exception $e) {
            $this->logger->error('Failed to save company.', [
                'user_id' => $user->getId() ?? "null",
                'error' => $e->getMessage()
            ]);

            return $this->json(['message' => 'ERROR_SAVING_COMPANY'], 500);
        }
    }
}
