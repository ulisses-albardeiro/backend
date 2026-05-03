<?php

namespace App\Service;

use App\Entity\User;
use App\Entity\Company;
use App\Mapper\CompanyMapper;
use App\DTO\Request\CompanyInputDTO;
use App\DTO\Response\CompanyOutputDTO;
use App\Service\Labor\LaborCategoryService;
use App\Service\Labor\LaborService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class CompanyService
{
    public function __construct(
        private CompanyMapper $mapper,
        private FileService $fileService,
        private EntityManagerInterface $entityManager,
        private LaborCategoryService $laborCategoryService,
        private LaborService $laborService,
    ) {}

    public function handleUpsert(User $user, CompanyInputDTO $inputDto, ?UploadedFile $logoFile): CompanyOutputDTO
    {
        $isNewAccount = ($user->getCompany() === null);

        $currentCompany = $user->getCompany();
        $oldLogoName = $currentCompany ? $currentCompany->getLogo() : null;

        $company = $this->upsertCompany($inputDto, $user, $currentCompany);

        if ($isNewAccount) {
            $user->setCompany($company);
            $this->setupNewAccount($company);
        }

        $subDir = $this->getSubDir($company);

        if ($logoFile) {
            if ($oldLogoName) {
                $this->fileService->remove($subDir, $oldLogoName);
            }

            $fileName = $this->fileService->upload($logoFile, $subDir);
            $company->setLogo($fileName);

            // Flush para salvar o nome do logo
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

        $subDir = $this->getSubDir($company);

        return $this->mapper->toOutputDto($company, $this->fileService->getPublicUrl($subDir, $company->getLogo()));
    }

    private function getSubDir(Company $company): string
    {
        if ($company->getCreatedAt()) {
            return 'company_' . md5($company->getCreatedAt()->format('U')) . '/logo';
        }

        return '';
    }

    private function setupNewAccount(Company $company)
    {
        $this->laborCategoryService->createDefaultCategories($company);
        $this->laborService->createDefaultLabors($company);
    }
}
