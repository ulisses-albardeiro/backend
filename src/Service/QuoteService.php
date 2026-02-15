<?php

namespace App\Service;

use App\Entity\Quote;
use App\Entity\Company;
use App\Mapper\QuoteMapper;
use App\Mapper\CompanyMapper;
use App\Mapper\CustomerMapper;
use App\DTO\Request\QuoteInputDTO;
use App\Repository\QuoteRepository;
use App\DTO\Response\QuoteOutputDTO;
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

    public function create(QuoteInputDTO $dto, Company $company): QuoteOutputDTO
    {
        $quote = $this->mapper->toEntity($dto, $company);

        $quote->recalculateTotals();

        $this->em->persist($quote);
        $this->em->flush();

        return $this->mapper->toOutputDTO($quote);
    }

    public function update(int $id, QuoteInputDTO $dto, Company $company): QuoteOutputDTO
    {
        $quote = $this->repository->findByIdAndCompany($id, $company);

        if (!$quote) {
            throw new NotFoundHttpException('QUOTE_NOT_FOUND');
        }

       $this->mapper->toEntity($dto, $company, $quote);

        $quote->recalculateTotals();

        $this->em->flush();

        return $this->mapper->toOutputDTO($quote);
    }

    public function delete(int $id, Company $company): void
    {
        $quote = $this->getByIdAndCompany($id, $company);

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
        $logoBase64 = $this->fileService->getBase64($this->getSubDir($company), $company->getLogo());

        $companyDto = $this->companyMapper->toOutputDTO($company, $logoBase64);
        $customerDto = $this->customerMapper->toOutputDTO($quoteEntity->getCustomer());

        return new QuoteDocument($quoteDto, $companyDto, $customerDto);
    }

    private function getSubDir(Company $company): string
    {
        if ($company->getCreatedAt()) {
            return 'company_' . md5($company->getCreatedAt()->format('U')) . '/logo';
        }

        return '';
    }
}
