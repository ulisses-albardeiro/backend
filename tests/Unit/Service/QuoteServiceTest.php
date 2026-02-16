<?php

namespace App\Tests\Unit\Service;

use App\Entity\Quote;
use App\Entity\Company;
use App\Mapper\QuoteMapper;
use App\Service\FileService;
use App\Mapper\CompanyMapper;
use App\Mapper\CustomerMapper;
use App\Service\QuoteService;
use App\DTO\Request\QuoteInputDTO;
use App\Repository\QuoteRepository;
use App\DTO\Response\QuoteOutputDTO;
use PHPUnit\Framework\TestCase;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;

#[AllowMockObjectsWithoutExpectations]
class QuoteServiceTest extends TestCase
{
    private $service;
    private $quoteRepo;
    private $quoteMapper;
    private $fileService;
    private $entityManager;
    private $companyMapper;
    private $customerMapper;

    protected function setUp(): void
    {
        $this->quoteRepo = $this->createMock(QuoteRepository::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->quoteMapper = $this->createMock(QuoteMapper::class);
        $this->fileService = $this->createMock(FileService::class);
        $this->companyMapper = $this->createMock(CompanyMapper::class);
        $this->customerMapper = $this->createMock(CustomerMapper::class);

        $this->service = new QuoteService(
            $this->quoteMapper,
            $this->fileService,
            $this->entityManager,
            $this->quoteRepo,
            $this->companyMapper,
            $this->customerMapper
        );
    }

    public function testListAllByCompanyReturnsMappedDtos(): void
    {
        $company = new Company();
        $quotes = [$this->createMock(Quote::class), $this->createMock(Quote::class)];

        $this->quoteRepo->expects($this->once())
            ->method('findBy')
            ->with(['company' => $company], ['date' => 'DESC'])
            ->willReturn($quotes);

        $this->quoteMapper->expects($this->exactly(2))
            ->method('toOutputDTO')
            ->willReturn($this->createMock(QuoteOutputDTO::class));

        $result = $this->service->listAllByCompany($company);

        $this->assertIsArray($result);
        $this->assertCount(2, $result);
    }

    public function testGetByIdAndCompanyThrowsNotFound(): void
    {
        $company = new Company();
        $this->quoteRepo->method('findByIdAndCompany')->willReturn(null);

        $this->expectException(NotFoundHttpException::class);
        $this->expectExceptionMessage('QUOTE_NOT_FOUND');

        $this->service->getByIdAndCompany(99, $company);
    }

    public function testCreatePersistsAndRecalculatesTotals(): void
    {
        $company = new Company();
        $dto = new QuoteInputDTO();
        $quote = $this->createMock(Quote::class);
        $outputDto = $this->createMock(QuoteOutputDTO::class);

        $this->quoteMapper->expects($this->once())
            ->method('toEntity')
            ->with($dto, $company)
            ->willReturn($quote);

        $quote->expects($this->once())->method('recalculateTotals');

        $this->entityManager->expects($this->once())->method('persist')->with($quote);
        $this->entityManager->expects($this->once())->method('flush');

        $this->quoteMapper->method('toOutputDTO')->with($quote)->willReturn($outputDto);

        $result = $this->service->create($dto, $company);

        $this->assertSame($outputDto, $result);
    }

    public function testUpdateModifiesExistingQuote(): void
    {
        $id = 1;
        $company = new Company();
        $dto = new QuoteInputDTO();
        $quote = $this->createMock(Quote::class);
        $outputDto = $this->createMock(QuoteOutputDTO::class);

        $this->quoteRepo->method('findByIdAndCompany')
            ->with($id, $company)
            ->willReturn($quote);

        $this->quoteMapper->expects($this->once())
            ->method('toEntity')
            ->with($dto, $company, $quote);

        $quote->expects($this->once())->method('recalculateTotals');
        $this->entityManager->expects($this->once())->method('flush');
        $this->quoteMapper->method('toOutputDTO')->willReturn($outputDto);

        $result = $this->service->update($id, $dto, $company);

        $this->assertSame($outputDto, $result);
    }

    public function testDeleteRemovesQuote(): void
    {
        $id = 4;
        $company = new Company();
        $quote = $this->createMock(Quote::class);
        
        $this->quoteRepo->method('findByIdAndCompany')->willReturn($quote);

        $this->entityManager->expects($this->once())->method('remove')->with($quote);
        $this->entityManager->expects($this->once())->method('flush');

        $this->service->delete($id, $company);
    }
}
