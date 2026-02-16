<?php

namespace App\Controller;

use App\Entity\User;
use Psr\Log\LoggerInterface;
use App\Service\ReceiptService;
use App\DTO\Request\ReceiptInputDTO;
use App\Service\Pdf\PdfGeneratorService;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

#[IsGranted('IS_AUTHENTICATED_FULLY')]
#[Route('/api/receipt', name: 'api_receipt_', format: 'json')]
final class ReceiptController extends AbstractController
{
    public function __construct(
        private ReceiptService $service,
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
            $receipt = $this->service->getByIdAndCompany($id, $user->getCompany());

            return $this->json($receipt);
        } catch (NotFoundHttpException $e) {
            return $this->json(['message' => $e->getMessage()], 404);
        }
    }

    #[Route('', name: 'store', methods: ['POST'])]
    public function store(#[MapRequestPayload] ReceiptInputDTO $dto): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        try {
            $receipt = $this->service->create($dto, $user->getCompany());
            return $this->json($receipt, 201);
        } catch (NotFoundHttpException $e) {
            return $this->json(['message' => $e->getMessage()], 404);
        } catch (BadRequestHttpException $e) {
            return $this->json(['message' => $e->getMessage()], 400);
        } catch (\Exception $e) {
            $this->logger->error('Failed to save receipt.', [
                'user_id' => $user->getId(),
                'error' => $e->getMessage()
            ]);
            return $this->json(['message' => 'ERROR_SAVING_RECEIPT'], 500);
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
            $this->logger->error('Delete receipt error', [
                'receipt_id' => $id,
                'user_id' => $user->getId(),
                'error' => $e->getMessage()
            ]);
            return $this->json(['message' => 'ERROR_DELETING_RECEIPT'], 500);
        }
    }

    #[Route('/{id}/pdf', name: 'pdf', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function downloadPdf(int $id, PdfGeneratorService $pdfGenerator): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        try {
            $document = $this->service->getReceiptDocument($id, $user->getCompany());
            $pdfBinary = $pdfGenerator->generate($document);

            return new Response($pdfBinary, 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="' . $document->getFileName() . '"'
            ]);
        } catch (NotFoundHttpException $e) {
            return $this->json(['message' => $e->getMessage()], 404);
        } catch (\Exception $e) {
            $this->logger->error('Receipt PDF Generation Error', [
                'receipt_id' => $id,
                'error' => $e->getMessage()
            ]);
            return $this->json(['message' => 'ERROR_GENERATING_PDF'], 500);
        }
    }
}
