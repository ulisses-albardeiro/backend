<?php

namespace App\Tests\Unit\Service;

use App\Entity\Company;
use App\Entity\Category;
use App\Enum\TransactionType;
use App\Mapper\CategoryMapper;
use PHPUnit\Framework\TestCase;
use App\Service\CategoryService;
use App\DTO\Request\CategoryInputDTO;
use App\Repository\CategoryRepository;
use App\DTO\Response\CategoryOutputDTO;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;

#[AllowMockObjectsWithoutExpectations]
class CategoryServiceTest extends TestCase
{
    private $mapper;
    private $service;
    private $repository;
    private $entityManager;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(CategoryRepository::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->mapper = $this->createMock(CategoryMapper::class);

        $this->service = new CategoryService(
            $this->mapper,
            $this->entityManager,
            $this->repository
        );
    }

    public function testListAllByCompanyReturnsMappedDtos(): void
    {
        $company = new Company();
        $cat1 = new Category();
        $cat2 = new Category();

        $this->repository->expects($this->once())
            ->method('findCategoryTree')
            ->with($company)
            ->willReturn([$cat1, $cat2]);

        $this->mapper->expects($this->exactly(2))
            ->method('toOutputDTO')
            ->willReturn($this->createMock(CategoryOutputDTO::class));

        $result = $this->service->listAllByCompany($company);

        $this->assertIsArray($result);
        $this->assertCount(2, $result);
    }

    public function testGetByIdAndCompanyReturnsOutputDtoWhenFound(): void
    {
        $company = new Company();
        $category = new Category();
        $dto = $this->createMock(CategoryOutputDTO::class);

        $this->repository->expects($this->once())
            ->method('findOneBy')
            ->willReturn($category);

        $this->mapper->expects($this->once())
            ->method('toOutputDTO')
            ->with($category)
            ->willReturn($dto);

        $result = $this->service->getByIdAndCompany(1, $company);

        $this->assertSame($dto, $result);
    }

    public function testCreateThrowsExceptionWhenTypesAreIncompatible(): void
    {
        $company = new Company();
        $dto = $this->createMock(CategoryInputDTO::class);
        
        $parent = new Category();
        $parent->setType(TransactionType::INCOME);

        $category = new Category();
        $category->setParent($parent);
        $category->setType(TransactionType::EXPENSE);

        $this->mapper->method('toEntity')->willReturn($category);

        $this->expectException(BadRequestHttpException::class);
        $this->expectExceptionMessage('INCOMPATIBLE_SUBCATEGORY');

        $this->service->create($dto, $company);
    }

    public function testCreatePersistsAndReturnsDto(): void
    {
        $company = new Company();
        $dto = $this->createMock(CategoryInputDTO::class);
        $category = new Category();
        $outputDto = $this->createMock(CategoryOutputDTO::class);

        $this->mapper->method('toEntity')->willReturn($category);
        $this->mapper->method('toOutputDTO')->willReturn($outputDto);

        $this->entityManager->expects($this->once())->method('persist')->with($category);
        $this->entityManager->expects($this->once())->method('flush');

        $result = $this->service->create($dto, $company);

        $this->assertSame($outputDto, $result);
    }

    public function testUpdateThrowsExceptionOnNotFound(): void
    {
        $company = new Company();
        $dto = $this->createMock(CategoryInputDTO::class);

        $this->repository->method('findByIdAndCompany')->willReturn(null);

        $this->expectException(NotFoundHttpException::class);
        $this->expectExceptionMessage('CATEGORY_NOT_FOUND');

        $this->service->update(999, $dto, $company);
    }

    public function testDeleteRemovesCategory(): void
    {
        $company = new Company();
        $category = new Category();

        $this->repository->method('findByIdAndCompany')->willReturn($category);

        $this->entityManager->expects($this->once())->method('remove')->with($category);
        $this->entityManager->expects($this->once())->method('flush');

        $this->service->delete(1, $company);
    }
}
