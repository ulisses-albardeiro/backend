<?php

namespace App\Service;

use App\Entity\Company;
use App\Mapper\ReceiptMapper;
use App\Mapper\CompanyMapper;
use App\Mapper\CustomerMapper;
use App\Repository\QuoteRepository;
use App\DTO\Request\ReceiptInputDTO;
use App\Repository\ReceiptRepository;
use App\DTO\Response\ReceiptOutputDTO;
use App\Repository\CustomerRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Service\Pdf\Documents\ReceiptDocument;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ReceiptService
{
    public function __construct(
        private ReceiptMapper $mapper,
        private FileService $fileService,
        private EntityManagerInterface $em,
        private CompanyMapper $companyMapper,
        private ReceiptRepository $repository,
        private CustomerMapper $customerMapper,
        private QuoteRepository $quoteRepository,
        private CustomerRepository $customerRepository,
    ) {}

    public function listAllByCompany(Company $company): array
    {
        $receipts = $this->repository->findBy(
            ['company' => $company],
            ['paymentDate' => 'DESC']
        );

        return array_map(fn($r) => $this->mapper->toOutputDTO($r), $receipts);
    }

    public function getByIdAndCompany(int $id, Company $company): ReceiptOutputDTO
    {
        $receipt = $this->repository->findOneBy(['id' => $id, 'company' => $company]);

        if (!$receipt) {
            throw new NotFoundHttpException('RECEIPT_NOT_FOUND');
        }

        return $this->mapper->toOutputDTO($receipt);
    }

    public function create(ReceiptInputDTO $dto, Company $company): ReceiptOutputDTO
    {
        $customer = $this->customerRepository->findOneBy([
            'id' => $dto->customerId, 
            'company' => $company
        ]);

        if (!$customer) {
            throw new NotFoundHttpException('CUSTOMER_NOT_FOUND');
        }

        $quote = null;
        if ($dto->quoteId) {
            $quote = $this->quoteRepository->findOneBy([
                'id' => $dto->quoteId, 
                'company' => $company
            ]);
        }

        $receipt = $this->mapper->toEntity($dto, $company, $customer, $quote);

        $this->em->persist($receipt);
        $this->em->flush();

        return $this->mapper->toOutputDTO($receipt);
    }

    public function delete(int $id, Company $company): void
    {
        $receipt = $this->repository->findOneBy(['id' => $id, 'company' => $company]);

        if ($receipt) {
            $this->em->remove($receipt);
            $this->em->flush();
        }
    }

    public function getReceiptDocument(int $id, Company $company): ReceiptDocument
    {
        $receiptEntity = $this->repository->findOneBy(['id' => $id, 'company' => $company]);

        if (!$receiptEntity) {
            throw new NotFoundHttpException('RECEIPT_NOT_FOUND');
        }

        $receiptDto = $this->mapper->toOutputDTO($receiptEntity);
        
        $logoBase64 = $this->fileService->getBase64($company->getSubDir('/logo'), $company->getLogo());

        $companyDto = $this->companyMapper->toOutputDTO($company, $logoBase64);
        $customerDto = $this->customerMapper->toOutputDTO($receiptEntity->getCustomer());

        return new ReceiptDocument($receiptDto, $companyDto, $customerDto);
    }
}
