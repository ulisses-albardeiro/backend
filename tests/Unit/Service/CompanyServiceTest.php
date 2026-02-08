<?php
namespace App\Tests\Unit\Service;

use App\Entity\User;
use App\Entity\Company;
use App\Service\FileService;
use App\Mapper\CompanyMapper;
use App\Service\CompanyService;
use PHPUnit\Framework\TestCase;
use App\DTO\Request\CompanyInputDTO;
use App\DTO\Response\CompanyOutputDTO;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;

#[AllowMockObjectsWithoutExpectations]
class CompanyServiceTest extends TestCase
{
    private $mapper;
    private $fileService;
    private $entityManager;
    private $service;

    protected function setUp(): void
    {
        $this->mapper = $this->createMock(CompanyMapper::class);
        $this->fileService = $this->createMock(FileService::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);

        $this->service = new CompanyService(
            $this->mapper, 
            $this->fileService, 
            $this->entityManager
        );
    }

    public function testHandleUpsertCreatesNewCompanyWhenUserHasNone(): void
    {
        $user = new User();
        $dto = $this->createFullDto();
        $company = new Company();

        $this->mapper->method('toEntity')->willReturn($company);
        $this->mapper->method('toOutputDto')->willReturn($this->createStub(CompanyOutputDTO::class));

        $this->entityManager->expects($this->once())->method('persist')->with($company);
        $this->entityManager->expects($this->once())->method('flush');

        $this->service->handleUpsert($user, $dto, null);

        $this->assertSame($company, $user->getCompany(), 'The company must be linked to the user.');
    }

    public function testHandleUpsertUpdatesExistingCompanyWithoutLogoChange(): void
    {
        $user = new User();
        $company = new Company();
        $company->setLogo('logo_existente.png');
        $user->setCompany($company);

        $dto = $this->createFullDto();

        $this->mapper->method('toEntity')->willReturn($company);
        $this->mapper->method('toOutputDto')->willReturn($this->createStub(CompanyOutputDTO::class));

        $this->entityManager->expects($this->once())->method('flush');
        $this->fileService->expects($this->never())->method('upload');

        $this->service->handleUpsert($user, $dto, null);

        $this->assertEquals('logo_existente.png', $company->getLogo());
    }

    public function testHandleUpsertDeletesOldLogoWhenNewOneIsUploaded(): void
    {
        $user = new User();
        $company = new Company();
        
        $reflection = new \ReflectionClass($company);
        $idProp = $reflection->getProperty('id');
        $idProp->setValue($company, 1);

        $company->setLogo('logo_antiga.png');
        $user->setCompany($company);

        $dto = $this->createFullDto();
        $uploadedFile = $this->createMock(UploadedFile::class);

        $this->mapper->method('toEntity')->willReturn($company);
        $this->mapper->method('toOutputDto')->willReturn($this->createStub(CompanyOutputDTO::class));

        $this->fileService->expects($this->once())
            ->method('remove')
            ->with('company_1/logo', 'logo_antiga.png');

        $this->fileService->method('upload')->willReturn('nova_logo.jpg');

        $this->service->handleUpsert($user, $dto, $uploadedFile);

        $this->assertEquals('nova_logo.jpg', $company->getLogo());
    }

    private function createFullDto(): CompanyInputDTO
    {
        return new CompanyInputDTO(
            name: 'Empresa Teste',
            tradingName: 'Nome Fantasia',
            registrationNumber: '12345678000100',
            stateRegistration: '123456789',
            email: 'contato@empresa.com',
            phone: '11999999999',
            website: 'https://empresa.com',
            zipCode: '01001-000',
            street: 'Avenida Paulista',
            number: '1000',
            complement: 'Conjunto 10',
            neighborhood: 'Bela Vista',
            city: 'São Paulo',
            state: 'SP'
        );
    }
}