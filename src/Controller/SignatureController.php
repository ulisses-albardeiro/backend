<?php

namespace App\Controller;

use App\Entity\User;
use Psr\Log\LoggerInterface;
use App\Service\SignatureService;
use App\Service\TechnicianService;
use App\DTO\Request\SignatureInputDTO;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

#[IsGranted('IS_AUTHENTICATED_FULLY')]
#[Route('/api/signature', name: 'api_signature_', format: 'json')]
final class SignatureController extends AbstractController
{
    public function __construct(
        private SignatureService $service,
        private TechnicianService $technicianService,
        private LoggerInterface $logger,
    ) {}

    #[Route('', name: 'save', methods: ['POST', 'PUT'])]
    public function save(#[MapRequestPayload] SignatureInputDTO $dto, Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();
        $signatureFile = $request->files->get('signature');

        if (!$signatureFile) {
            return $this->json(['message' => 'SIGNATURE_FILE_REQUIRED'], 400);
        }

        try {
            $technician = $this->service->upsert($dto->technicianId, $user->getCompany(), $signatureFile);

            return $this->json($this->technicianService->toOutputDTO($technician));
        } catch (NotFoundHttpException $e) {
            return $this->json(['message' => $e->getMessage()], 404);
        } catch (\Exception $e) {
            $this->logger->error('Failed to save signature.', [
                'user_id' => $user->getId(),
                'error' => $e->getMessage(),
            ]);

            return $this->json(['message' => 'ERROR_SAVING_SIGNATURE'], 500);
        }
    }

    #[Route('/{technicianId}', name: 'delete', methods: ['DELETE'], requirements: ['technicianId' => '\d+'])]
    public function delete(int $technicianId): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        try {
            $this->service->delete($technicianId, $user->getCompany());

            return $this->json(null, 204);
        } catch (NotFoundHttpException $e) {
            return $this->json(['message' => $e->getMessage()], 404);
        } catch (\Exception $e) {
            $this->logger->error('Delete signature error', [
                'technician_id' => $technicianId,
                'user_id' => $user->getId(),
                'error' => $e->getMessage(),
            ]);

            return $this->json(['message' => 'ERROR_DELETING_SIGNATURE'], 500);
        }
    }
}
