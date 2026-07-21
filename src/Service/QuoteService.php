<?php

namespace App\Service;

use App\Entity\Company;
use App\Mapper\Quote\QuoteMapper;
use App\Mapper\CompanyMapper;
use App\Mapper\Customer\CustomerMapper;
use App\DTO\Request\Quote\QuoteInputDTO;
use App\Repository\QuoteRepository;
use App\DTO\Response\Quote\QuoteOutputDTO;
use App\Repository\Customer\CustomerAssetRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Service\Pdf\Documents\QuoteDocument;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class QuoteService
{
    public function __construct(
        private QuoteMapper $mapper,
        private FileService $fileService,
        private EntityManagerInterface $em,
        private QuoteRepository $repository,
        private CompanyMapper $companyMapper,
        private CustomerMapper $customerMapper,
        private CustomerAssetRepository $assetRepository,
        private QuoteItemImageService $quoteItemImageService,
    ) {}

    public function listAllByCompany(Company $company): array
    {
        $quotes = $this->repository->findBy(
            ['company' => $company],
            ['date' => 'DESC']
        );

        return array_map(fn($q) => $this->mapper->toOutputDTO($q), $quotes);
    }

    public function getByIdAndCompany(int $id, Company $company): QuoteOutputDTO
    {
        $quote = $this->repository->findByIdAndCompany($id, $company);

        if (!$quote) {
            throw new NotFoundHttpException('QUOTE_NOT_FOUND');
        }

        return $this->mapper->toOutputDTO($quote);
    }

    public function create(QuoteInputDTO $dto, Company $company, array $itemImageFiles = []): QuoteOutputDTO
    {
        $asset = $this->assetRepository->findOneBy([
            'id' => $dto->assetId,
            'company' => $company
        ]);
        $quote = $this->mapper->toEntity($dto, $company, $asset, null, $itemImageFiles);

        $quote->recalculateTotals();

        $this->em->persist($quote);
        $this->em->flush();

        return $this->mapper->toOutputDTO($quote);
    }

    public function update(int $id, QuoteInputDTO $dto, Company $company, array $itemImageFiles = []): QuoteOutputDTO
    {
        $quote = $this->repository->findByIdAndCompany($id, $company);

        if (!$quote) {
            throw new NotFoundHttpException('QUOTE_NOT_FOUND');
        }

        $asset = $this->assetRepository->findOneBy([
            'id' => $dto->assetId,
            'company' => $company
        ]);

        $this->mapper->toEntity($dto, $company, $asset, $quote, $itemImageFiles);

        $quote->recalculateTotals();

        $this->em->flush();

        return $this->mapper->toOutputDTO($quote);
    }

    public function delete(int $id, Company $company): void
    {
        $quote = $this->repository->findByIdAndCompany($id, $company);

        if (!$quote) {
            return;
        }

        $this->em->remove($quote);
        $this->em->flush();
    }

    public function getQuoteDocument(int $id, Company $company): QuoteDocument
    {
        $quoteEntity = $this->repository->findByIdAndCompany($id, $company);

        if (!$quoteEntity) {
            throw new NotFoundHttpException('QUOTE_NOT_FOUND');
        }

        $quoteDto = $this->mapper->toOutputDTO($quoteEntity);
        $logoBase64 = $this->fileService->getBase64($company->getSubDir('/logo'), $company->getLogo());

        $companyDto = $this->companyMapper->toOutputDTO($company, $logoBase64);
        $customerDto = $this->customerMapper->toOutputDTO($quoteEntity->getCustomer());

        $itemImagesSubDir = $this->quoteItemImageService->getSubDir($company);
        $photosByItemId = [];
        foreach ($quoteEntity->getQuoteItems() as $item) {
            foreach ($item->getImages() as $image) {
                $photosByItemId[$item->getId()][] = $this->fileService->getBase64($itemImagesSubDir, $image->getPath());
            }
        }

        return new QuoteDocument($quoteDto, $companyDto, $customerDto, $photosByItemId);
    }
}
