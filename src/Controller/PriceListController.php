<?php

namespace App\Controller;

use App\Entity\User;
use Psr\Log\LoggerInterface;
use App\Service\PriceListService;
use App\DTO\Request\PriceListInputDTO;
use App\Service\Pdf\PdfGeneratorService;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[IsGranted('IS_AUTHENTICATED_FULLY')]
#[Route('/api/price-list', name: 'api_price_list_', format: 'json')]
final class PriceListController extends AbstractController
{
    public function __construct(
        private LoggerInterface $logger,
        private PriceListService $service,
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
            return $this->json($this->service->getByIdAndCompany($id, $user->getCompany()));
        } catch (NotFoundHttpException $e) {
            return $this->json(['message' => 'PRICE_LIST_NOT_FOUND'], 404);
        }
    }

    #[Route('', name: 'store', methods: ['POST'])]
    public function store(#[MapRequestPayload] PriceListInputDTO $dto): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        try {
            $priceList = $this->service->create($dto, $user->getCompany());
            return $this->json($priceList, 201);
        } catch (\Exception $e) {
            $this->logger->error('Failed to save price list.', [
                'user_id' => $user->getId(),
                'error' => $e->getMessage()
            ]);
            return $this->json(['message' => 'ERROR_SAVING_PRICE_LIST'], 500);
        }
    }

    #[Route('/{id}', name: 'update', methods: ['PUT'], requirements: ['id' => '\d+'])]
    public function update(int $id, #[MapRequestPayload] PriceListInputDTO $dto): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        try {
            $priceList = $this->service->update($id, $dto, $user->getCompany());
            return $this->json($priceList, 200);
        } catch (NotFoundHttpException $e) {
            return $this->json(['message' => 'PRICE_LIST_NOT_FOUND'], 404);
        } catch (\Exception $e) {
            $this->logger->error('Update price list error', [
                'price_list_id' => $id,
                'user_id' => $user->getId(),
                'error' => $e->getMessage()
            ]);
            return $this->json(['message' => 'ERROR_UPDATING_PRICE_LIST'], 500);
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
        } catch (\Exception $e) {
            $this->logger->error('Delete price list error', [
                'price_list_id' => $id,
                'user_id' => $user->getId(),
                'error' => $e->getMessage()
            ]);
            return $this->json(['message' => 'ERROR_DELETING_PRICE_LIST'], 500);
        }
    }

    #[Route('/{id}/pdf', name: 'pdf', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function downloadPdf(int $id, PdfGeneratorService $pdfGenerator): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        try {
            $document = $this->service->getPriceListDocument($id, $user->getCompany());
            $pdfBinary = $pdfGenerator->generate($document);

            return new Response($pdfBinary, 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="' . $document->getFileName() . '"'
            ]);
        } catch (NotFoundHttpException $e) {
            return $this->json(['message' => 'PRICE_LIST_NOT_FOUND'], 404);
        } catch (\Exception $e) {
            $this->logger->error('PDF Generation Error for PriceList', [
                'id' => $id,
                'error' => $e->getMessage()
            ]);
            return $this->json(['message' => 'ERROR_GENERATING_PDF'], 500);
        }
    }
}
