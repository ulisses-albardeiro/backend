<?php

namespace App\Tests\Unit\Service;

use App\Entity\Company;
use App\Entity\Customer;
use App\Mapper\CustomerMapper;
use PHPUnit\Framework\TestCase;
use App\Service\CustomerService;
use App\DTO\Request\CustomerInputDTO;
use App\Repository\CustomerRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;

#[AllowMockObjectsWithoutExpectations]
class CustomerServiceTest extends TestCase
{
    private $repository;
    private $entityManager;
    private $mapper;
    private $service;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(CustomerRepository::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->mapper = $this->createMock(CustomerMapper::class);

        $this->service = new CustomerService(
            $this->mapper,
            $this->entityManager,
            $this->repository,
        );
    }

    public function testListAllByCompanyReturnsMappedDtos(): void
    {
        $company = new Company();
        $customer1 = new Customer();
        $customer2 = new Customer();

        $this->repository->expects($this->once())
            ->method('findBy')
            ->with(['company' => $company], ['name' => 'ASC'])
            ->willReturn([$customer1, $customer2]);

        $this->mapper->expects($this->exactly(2))
            ->method('toOutputDTO')
            ->willReturn($this->createMock(\App\DTO\Response\CustomerOutputDTO::class));

        $result = $this->service->listAllByCompany($company);

        $this->assertIsArray($result);
        $this->assertCount(2, $result);
    }

    public function testGetByIdAndCompanyReturnsCustomerWhenFound(): void
    {
        $company = new Company();
        $customer = new Customer();
        $customerId = 1;

        $this->repository->expects($this->once())
            ->method('findOneBy')
            ->with(['id' => $customerId, 'company' => $company])
            ->willReturn($customer);

        $result = $this->service->getByIdAndCompany($customerId, $company);

        $this->assertSame($customer, $result);
    }

    public function testGetByIdAndCompanyThrowsNotFoundExceptionWhenNotFound(): void
    {
        $company = new Company();
        $this->repository->method('findOneBy')->willReturn(null);

        $this->expectException(NotFoundHttpException::class);
        $this->expectExceptionMessage('CUSTOMER_NOT_FOUND');

        $this->service->getByIdAndCompany(999, $company);
    }

    public function testCreatePersistsNewCustomer(): void
    {
        $company = new Company();
        $dto = $this->createMock(CustomerInputDTO::class);
        $customer = new Customer();

        $this->mapper->expects($this->once())
            ->method('toEntity')
            ->with($dto, $company)
            ->willReturn($customer);

        $this->entityManager->expects($this->once())->method('persist')->with($customer);
        $this->entityManager->expects($this->once())->method('flush');

        $result = $this->service->create($dto, $company);

        $this->assertSame($customer, $result);
    }

    public function testDeleteRemovesCustomerFromDatabase(): void
    {
        $company = new Company();
        $customer = new Customer();
        $customerId = 1;

        $this->repository->method('findOneBy')->willReturn($customer);

        $this->entityManager->expects($this->once())->method('remove')->with($customer);
        $this->entityManager->expects($this->once())->method('flush');

        $this->service->delete($customerId, $company);
    }
}
