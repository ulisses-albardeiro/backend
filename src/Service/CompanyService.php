<?php

namespace App\Service;

use App\Entity\User;
use App\Mapper\CompanyMapper;
use App\DTO\Request\CompanyInputDTO;
use App\DTO\Response\CompanyOutputDTO;
use Doctrine\ORM\EntityManagerInterface;

class CompanyService
{
    public function __construct(
        private CompanyMapper $mapper,
        private EntityManagerInterface $entityManager,
    ) {}

    /**
     * Upsert Logic (Update or Register)
     */
    public function upsertCompany(CompanyInputDTO $dto, User $user): CompanyOutputDTO
    {
        $company = $user->getCompany();
        
        $company = $this->mapper->toEntity($dto, $user, $company);

        $this->entityManager->persist($company);
        $this->entityManager->flush();

        return $this->mapper->toOutputDTO($company);
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
