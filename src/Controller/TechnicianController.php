<?php

namespace App\Controller;

use App\Entity\User;
use Psr\Log\LoggerInterface;
use App\Service\TechnicianService;
use App\DTO\Request\TechnicianInputDTO;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

#[IsGranted('IS_AUTHENTICATED_FULLY')]
#[Route('/api/technician', name: 'api_technician_', format: 'json')]
final class TechnicianController extends AbstractController
{
    public function __construct(
        private TechnicianService $service,
        private LoggerInterface $logger,
    ) {}

    #[Route('', name: 'index', methods: ['GET'])]
    public function index(): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        return $this->json($this->service->listAllByCompany($user->getCompany()));
    }

    #[Route('/{id}', name: 'show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(int $id): JsonResponse
    {
        try {
            /** @var User $user */
            $user = $this->getUser();
            $technician = $this->service->getByIdAndCompany($id, $user->getCompany());

            return $this->json($this->service->toOutputDTO($technician));
        } catch (NotFoundHttpException $e) {
            return $this->json(['message' => $e->getMessage()], 404);
        }
    }

    #[Route('', name: 'store', methods: ['POST'])]
    public function store(#[MapRequestPayload] TechnicianInputDTO $dto): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        try {
            $technician = $this->service->create($dto, $user->getCompany());

            return $this->json($this->service->toOutputDTO($technician), 201);
        } catch (\Exception $e) {
            $this->logger->error('Failed to save technician.', [
                'user_id' => $user->getId(),
                'error' => $e->getMessage(),
            ]);

            return $this->json(['message' => 'ERROR_SAVING_TECHNICIAN'], 500);
        }
    }

    #[Route('/{id}', name: 'update', methods: ['PUT'], requirements: ['id' => '\d+'])]
    public function update(int $id, #[MapRequestPayload] TechnicianInputDTO $dto): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        try {
            $technician = $this->service->update($id, $dto, $user->getCompany());

            return $this->json($this->service->toOutputDTO($technician));
        } catch (NotFoundHttpException $e) {
            return $this->json(['message' => $e->getMessage()], 404);
        } catch (\Exception $e) {
            $this->logger->error('Update technician error', [
                'technician_id' => $id,
                'user_id' => $user->getId(),
                'error' => $e->getMessage(),
            ]);

            return $this->json(['message' => 'ERROR_UPDATING_TECHNICIAN'], 500);
        }
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'], requirements: ['id' => '\d+'])]
    public function delete(int $id): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        try {
            $this->service->delete($id, $user->getCompany());

            return $this->json(null, 204);
        } catch (NotFoundHttpException $e) {
            return $this->json(['message' => $e->getMessage()], 404);
        } catch (\Exception $e) {
            $this->logger->error('Delete technician error', [
                'technician_id' => $id,
                'user_id' => $user->getId(),
                'error' => $e->getMessage(),
            ]);

            return $this->json(['message' => 'ERROR_DELETING_TECHNICIAN'], 500);
        }
    }
}
