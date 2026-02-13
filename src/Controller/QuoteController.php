<?php

namespace App\Controller;

use App\Entity\User;
use Psr\Log\LoggerInterface;
use App\Service\QuoteService;
use App\DTO\Request\QuoteInputDTO;
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
#[Route('/api/quote', name: 'api_quote_', format: 'json')]
final class QuoteController extends AbstractController
{
    public function __construct(
        private LoggerInterface $logger,
        private QuoteService $service,
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
            $quote = $this->service->getByIdAndCompany($id, $user->getCompany());

            return $this->json($quote);
        } catch (NotFoundHttpException $e) {
            return $this->json(['message' => $e->getMessage()], 404);
        }
    }

    #[Route('', name: 'store', methods: ['POST'])]
    public function store(#[MapRequestPayload] QuoteInputDTO $dto): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        try {
            $quote = $this->service->create($dto, $user->getCompany());
            return $this->json($quote, 201);
        } catch (BadRequestHttpException $e) {
            return $this->json(['message' => $e->getMessage()], 400);
        } catch (\Exception $e) {
            $this->logger->error('Failed to save quote.', [
                'user_id' => $user->getId(),
                'error' => $e->getMessage()
            ]);
            return $this->json(['message' => 'ERROR_SAVING_QUOTE'], 500);
        }
    }

    #[Route('/{id}', name: 'update', methods: ['PUT'])]
    public function update(int $id, #[MapRequestPayload] QuoteInputDTO $dto): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        try {
            $quote = $this->service->update($id, $dto, $user->getCompany());
            return $this->json($quote, 200);
        } catch (NotFoundHttpException $e) {
            return $this->json(['message' => $e->getMessage()], 404);
        } catch (BadRequestHttpException $e) {
            return $this->json(['message' => $e->getMessage()], 400);
        } catch (\Exception $e) {
            $this->logger->error('Update quote error', [
                'quote_id' => $id,
                'user_id' => $user->getId(),
                'error' => $e->getMessage()
            ]);
            return $this->json(['message' => 'ERROR_UPDATING_QUOTE'], 500);
        }
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
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
            $this->logger->error('Delete quote error', [
                'quote_id' => $id,
                'user_id' => $user->getId(),
                'error' => $e->getMessage()
            ]);
            return $this->json(['message' => 'ERROR_DELETING_QUOTE'], 500);
        }
    }

    #[Route('/{id}/pdf', name: 'pdf', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function downloadPdf(int $id, PdfGeneratorService $pdfGenerator): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        try {
            $document = $this->service->getQuoteDocument($id, $user->getCompany());

            $pdfBinary = $pdfGenerator->generate($document);

            return new Response($pdfBinary, 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="' . $document->getFileName() . '"'
            ]);
        } catch (NotFoundHttpException $e) {
            return $this->json(['message' => $e->getMessage()], 404);
        } catch (\Exception $e) {
            $this->logger->error('PDF Generation Error', [
                'id' => $id,
                'error' => $e->getMessage()
            ]);
            return $this->json([
                'message' => 'ERROR_GENERATING_PDF'
            ], 500);
        }
    }
}
