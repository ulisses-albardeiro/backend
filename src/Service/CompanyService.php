<?php

namespace App\Service;

use App\Entity\User;
use App\Entity\Company;
use App\Mapper\CompanyMapper;
use App\DTO\Request\CompanyInputDTO;
use App\DTO\Response\CompanyOutputDTO;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class CompanyService
{
    public function __construct(
        private CompanyMapper $mapper,
        private FileService $fileService,
        private EntityManagerInterface $entityManager,
    ) {}

    public function handleUpsert(User $user, CompanyInputDTO $inputDto, ?UploadedFile $logoFile): CompanyOutputDTO
    {
        $currentCompany = $user->getCompany();
        $oldLogoName = $currentCompany ? $currentCompany->getLogo() : null;

        $company = $this->upsertCompany($inputDto, $user, $currentCompany);

        if ($user->getCompany() === null) {
            $user->setCompany($company);
        }

        if ($logoFile) {
            $subDir = 'company_' . md5($company->getId() . $company->getCreatedAt()->format('U')) . '/logo';

            if ($oldLogoName) {
                $this->fileService->remove($subDir, $oldLogoName);
            }

            $fileName = $this->fileService->upload($logoFile, $subDir);

            $company->setLogo($fileName);
            $this->entityManager->flush();
        }

        return $this->mapper->toOutputDto($company, $this->fileService->getPublicUrl($subDir, $company->getLogo()));
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

        $subDir = 'company_' . md5($company->getId() . $company->getCreatedAt()->format('U')) . '/logo';
        return $this->mapper->toOutputDto($company, $this->fileService->getPublicUrl($subDir, $company->getLogo()));
    }
}
