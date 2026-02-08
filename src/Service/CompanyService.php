<?php

namespace App\Service;

use App\Entity\User;
use App\Entity\Company;
use App\Mapper\CompanyMapper;
use App\DTO\Request\CompanyInputDTO;
use App\DTO\Response\CompanyOutputDTO;
use App\Exception\ValidationException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class CompanyService
{
    public function __construct(
        private CompanyMapper $mapper,
        private FileService $fileService,
        private EntityManagerInterface $entityManager,
        private readonly ValidatorInterface $validator,
        private readonly DenormalizerInterface $denormalizer,
    ) {}

    public function handleUpsert(User $user, array $data, ?UploadedFile $logoFile): CompanyOutputDTO
    {
        $inputDto = $this->denormalizer->denormalize($data, CompanyInputDTO::class);

        $errors = $this->validator->validate($inputDto);
        if (count($errors) > 0) {
            throw new ValidationException($errors);
        }

        $currentCompany = $user->getCompany();
        $oldLogoName = $currentCompany ? $currentCompany->getLogo() : null;

        $company = $this->upsertCompany($inputDto, $user, $currentCompany);

        if ($user->getCompany() === null) {
            $user->setCompany($company);
        }
        
        if ($logoFile) {
            $subDir = 'company_' . $company->getId() . '/logo';

            if ($oldLogoName) {
                $this->fileService->remove($subDir, $oldLogoName);
            }

            $fileName = $this->fileService->upload($logoFile, $subDir);

            $company->setLogo($fileName);
            $this->entityManager->flush();
        }

        return $this->mapper->toOutputDto($company);
    }

    /**
     * Upsert Logic (Update or Register)
     */
    public function upsertCompany(CompanyInputDTO $dto, User $user, ?Company $currentCompany): Company
    {
        $company = $this->mapper->toEntity($dto, $user, $currentCompany);

        $this->entityManager->persist($company);
        $this->entityManager->flush();

        return $company;
    }

    public function getCompanyByUser(User $user): ?CompanyOutputDTO
    {
        $company = $user->getCompany();

        if (!$company) {
            return null;
        }

        return $this->mapper->toOutputDTO($company);
    }
}
