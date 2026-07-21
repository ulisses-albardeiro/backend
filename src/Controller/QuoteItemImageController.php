<?php

namespace App\Controller;

use App\Entity\User;
use Psr\Log\LoggerInterface;
use App\Service\QuoteItemImageService;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[IsGranted('IS_AUTHENTICATED_FULLY')]
#[Route('/api/quote-item-image', name: 'api_quote_item_image_', format: 'json')]
final class QuoteItemImageController extends AbstractController
{
    public function __construct(
        private QuoteItemImageService $service,
        private LoggerInterface $logger,
    ) {}

    #[Route('/{id}', name: 'delete', methods: ['DELETE'], requirements: ['id' => '\d+'])]
    public function delete(int $id): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        try {
            $this->service->removeImage($id, $user->getCompany());
            return $this->json(null, 204);
        } catch (NotFoundHttpException $e) {
            return $this->json(['message' => $e->getMessage()], 404);
        } catch (\Exception $e) {
            $this->logger->error('Delete quote item image error', [
                'image_id' => $id,
                'user_id' => $user->getId(),
                'error' => $e->getMessage()
            ]);
            return $this->json(['message' => 'ERROR_DELETING_QUOTE_ITEM_IMAGE'], 500);
        }
    }
}
