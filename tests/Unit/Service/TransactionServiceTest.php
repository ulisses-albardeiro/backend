<?php

namespace App\Tests\Unit\Service;

use App\Entity\Company;
use App\Entity\Customer;
use App\Entity\Category;
use App\Entity\Transaction;
use PHPUnit\Framework\TestCase;
use App\Mapper\TransactionMapper;
use App\Service\TransactionService;
use App\Repository\CategoryRepository;
use App\Repository\CustomerRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\DTO\Request\TransactionInputDTO;
use App\Repository\TransactionRepository;
use App\DTO\Response\TransactionOutputDTO;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;

#[AllowMockObjectsWithoutExpectations]
class TransactionServiceTest extends TestCase
{
    private $transactionRepo;
    private $categoryRepo;
    private $customerRepo;
    private $entityManager;
    private $mapper;
    private $service;

    protected function setUp(): void
    {
        $this->transactionRepo = $this->createMock(TransactionRepository::class);
        $this->categoryRepo = $this->createMock(CategoryRepository::class);
        $this->customerRepo = $this->createMock(CustomerRepository::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->mapper = $this->createMock(TransactionMapper::class);

        $this->service = new TransactionService(
            $this->mapper,
            $this->entityManager,
            $this->customerRepo,
            $this->categoryRepo,
            $this->transactionRepo
        );
    }

    public function testListAllByCompanyReturnsMappedDtos(): void
    {
        $company = new Company();
        $transactions = [new Transaction(), new Transaction()];

        $this->transactionRepo->expects($this->once())
            ->method('findBy')
            ->with(['company' => $company], ['date' => 'DESC'])
            ->willReturn($transactions);

        $this->mapper->expects($this->exactly(2))
            ->method('toOutputDTO')
            ->willReturn($this->createMock(TransactionOutputDTO::class));

        $result = $this->service->listAllByCompany($company);

        $this->assertIsArray($result);
        $this->assertCount(2, $result);
    }

    public function testCreateThrowsExceptionWhenCategoryNotFound(): void
    {
        $company = new Company();
        $dto = new TransactionInputDTO();
        $dto->categoryId = 99;

        $this->categoryRepo->method('findOneBy')->willReturn(null);

        $this->expectException(BadRequestHttpException::class);
        $this->expectExceptionMessage('CATEGORY_NOT_FOUND_OR_NOT_APPLICABLE');

        $this->service->create($dto, $company);
    }

    public function testCreatePersistsTransactionWithValidData(): void
    {
        $company = new Company();
        $category = new Category();
        $customer = new Customer();
        $transaction = new Transaction();
        $outputDto = $this->createMock(TransactionOutputDTO::class);

        $dto = new TransactionInputDTO();
        $dto->categoryId = 1;
        $dto->customerId = 2;

        $this->categoryRepo->method('findOneBy')->willReturn($category);
        $this->customerRepo->method('findOneBy')->willReturn($customer);
        
        $this->mapper->expects($this->once())
            ->method('toEntity')
            ->with($dto, $company, $category, $customer)
            ->willReturn($transaction);

        $this->mapper->method('toOutputDTO')->with($transaction)->willReturn($outputDto);

        $this->entityManager->expects($this->once())->method('persist')->with($transaction);
        $this->entityManager->expects($this->once())->method('flush');

        $result = $this->service->create($dto, $company);

        $this->assertSame($outputDto, $result);
    }

    public function testUpdateModifiesExistingTransaction(): void
    {
        $id = 1;
        $company = new Company();
        $transaction = new Transaction();
        $category = new Category();
        $dto = new TransactionInputDTO();
        $dto->categoryId = 10;
        $outputDto = $this->createMock(TransactionOutputDTO::class);

        $this->transactionRepo->method('findOneBy')->willReturn($transaction);
        $this->categoryRepo->method('findOneBy')->willReturn($category);

        $this->mapper->expects($this->once())
            ->method('toEntity')
            ->with($dto, $company, $category, null, $transaction)
            ->willReturn($transaction);

        $this->mapper->method('toOutputDTO')->willReturn($outputDto);
        $this->entityManager->expects($this->once())->method('flush');

        $result = $this->service->update($id, $dto, $company);

        $this->assertSame($outputDto, $result);
    }

    public function testDeleteThrowsNotFoundWhenTransactionDoesNotExist(): void
    {
        $company = new Company();
        $this->transactionRepo->method('findOneBy')->willReturn(null);

        $this->expectException(NotFoundHttpException::class);
        $this->expectExceptionMessage('TRANSACTION_NOT_FOUND');

        $this->service->delete(999, $company);
    }
}
