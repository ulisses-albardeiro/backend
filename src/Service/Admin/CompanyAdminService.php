<?php

namespace App\Service\Admin;

use App\Entity\Company;
use App\Mapper\CompanyMapper;
use App\DTO\Response\CompanyOutputDTO;
use App\Repository\CompanyRepository;
use App\Service\FileService;

class CompanyAdminService
{
    public function __construct(
        private CompanyMapper $mapper,
        private FileService $fileService,
        private CompanyRepository $repository,
    ) {}

    /**
     * @return CompanyOutputDTO[]
     */
    public function getCompanies(): array
    {
        $companies = $this->repository->findAll();
        $output = [];

        foreach ($companies as $company) {
            $subDir = $this->getSubDir($company);
            $logoUrl = $this->fileService->getPublicUrl($subDir, $company->getLogo());

            $output[] = $this->mapper->toOutputDto($company, $logoUrl);
        }

        return $output;
    }

    private function getSubDir(Company $company): string
    {
        if ($company->getCreatedAt()) {
            return 'company_' . md5($company->getCreatedAt()->format('U')) . '/logo';
        }

        return '';
    }
}
