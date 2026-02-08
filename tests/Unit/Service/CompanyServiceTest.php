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
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;

class CompanyServiceTest extends TestCase
{
    #[AllowMockObjectsWithoutExpectations]
    public function testHandleUpsertDeletesOldLogoWhenNewOneIsUploaded(): void
    {
        $mapper = $this->createMock(CompanyMapper::class);
        $fileService = $this->createMock(FileService::class);
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $validator = $this->createMock(ValidatorInterface::class);
        $denormalizer = $this->createMock(DenormalizerInterface::class);

        $user = new User();
        $company = new Company();

        $reflection = new \ReflectionClass($company);
        $idProp = $reflection->getProperty('id');
        $idProp->setValue($company, 1);

        $company->setLogo('logo_antiga.png');
        $user->setCompany($company);

        $dto = new CompanyInputDTO(
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
        $uploadedFile = $this->createMock(UploadedFile::class);

        $denormalizer->method('denormalize')->willReturn($dto);

        $validator->method('validate')->willReturn(new ConstraintViolationList());

        $mapper->method('toEntity')->willReturn($company);
        $mapper->method('toOutputDto')->willReturn($this->createMock(CompanyOutputDTO::class));

        $fileService->expects($this->once())
            ->method('remove')
            ->with('company_1/logo', 'logo_antiga.png');

        $fileService->method('upload')->willReturn('nova_logo.jpg');

        $service = new CompanyService($mapper, $fileService, $entityManager, $validator, $denormalizer);
        $service->handleUpsert($user, ['some' => 'data'], $uploadedFile);

        $this->assertEquals('nova_logo.jpg', $company->getLogo());
    }
}
