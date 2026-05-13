<?php

namespace App\Controller\Customer;

use App\DTO\Request\Customer\CustomerAssetInputDTO;
use Psr\Log\LoggerInterface;
use App\Service\Customer\CustomerAssetService;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

#[IsGranted('IS_AUTHENTICATED_FULLY')]
#[Route('/api/customer-asset', name: 'api_customer_asset_', format: 'json')]
final class CustomerAssetController extends AbstractController
{
    public function __construct(
        private LoggerInterface $logger,
        private CustomerAssetService $service,
    ) {}

    #[Route('', name: 'index', methods: ['GET'])]
    public function index(): JsonResponse
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();

        return $this->json($this->service->listAllByCompany($user->getCompany()));
    }

    #[Route('/{id}', name: 'show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(int $id): JsonResponse
    {
        try {
            /** @var \App\Entity\User $user */
            $user = $this->getUser();
            $customerAsset = $this->service->getByIdAndCompany($id, $user->getCompany());
            return $this->json($customerAsset);
        } catch (NotFoundHttpException $e) {
            return $this->json(['message' => $e->getMessage()], 404);
        }
    }

    #[Route('', name: 'store', methods: ['POST'])]
    public function store(#[MapRequestPayload] CustomerAssetInputDTO $dto): JsonResponse
    {
        try {
            /** @var \App\Entity\User $user */
            $user = $this->getUser();

            $customerAsset = $this->service->create($dto, $user->getCompany());
            return $this->json($customerAsset, 201);
        } catch (NotFoundHttpException $e) {
            return $this->json(['message' => $e->getMessage()], 404);
        } catch (\Exception $e) {
            $this->logger->error('Failed to save custom.', [
                'user_id' => $user->getId(),
                'error' => $e->getMessage()
            ]);
            return $this->json([
                'message' => 'ERROR_SAVING_CUSTOM'
            ], 500);
        }
    }

    #[Route('/{id}', name: 'update', methods: ['PUT'], requirements: ['id' => '\d+'])]
    public function update(int $id, #[MapRequestPayload] CustomerAssetInputDTO $dto): JsonResponse
    {
        try {
            /** @var \App\Entity\User $user */
            $user = $this->getUser();

            $customerAsset = $this->service->update($id, $dto, $user->getCompany());
            return $this->json($customerAsset);
        } catch (NotFoundHttpException $e) {
            return $this->json(['message' => $e->getMessage()], 404);
        } catch (\Exception $e) {
            $this->logger->error('Update customer error', [
                'customer_id' => $id,
                'user_id' => $user->getId(),
                'error' => $e->getMessage()
            ]);
            return $this->json(['message' => 'ERROR_UPDATING_CUSTOMER'], 500);
        }
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        try {
            /** @var \App\Entity\User $user */
            $user = $this->getUser();

            $this->service->delete($id, $user->getCompany());

            return $this->json(null, 204);
        } catch (NotFoundHttpException $e) {
            return $this->json(['message' => $e->getMessage()], 404);
        } catch (\Exception $e) {
            $this->logger->error('Delete customer error', [
                'customer_id' => $id,
                'user_id' => $user->getId(),
                'error' => $e->getMessage()
            ]);
            return $this->json(['message' => 'ERROR_DELETING_CUSTOMER'], 500);
        }
    }
}
