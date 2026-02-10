<?php

namespace App\Mapper;

use App\Entity\User;
use App\Entity\Company;
use App\DTO\Request\CompanyInputDTO;
use App\DTO\Response\CompanyOutputDTO;

class CompanyMapper
{
    public function toEntity(CompanyInputDTO $dto, User $owner, ?Company $company = null): Company
    {
        $company ??= new Company();

        $company->setOwner($owner);
        $company->setName($dto->name);
        $company->setTradingName($dto->tradingName);
        $company->setRegistrationNumber($dto->registrationNumber);
        $company->setStateRegistration($dto->stateRegistration);
        $company->setEmail($dto->email);
        $company->setPhone($dto->phone);
        $company->setWebsite($dto->website);
        $company->setZipCode($dto->zipCode);
        $company->setStreet($dto->street);
        $company->setNumber($dto->number);
        $company->setComplement($dto->complement);
        $company->setNeighborhood($dto->neighborhood);
        $company->setCity($dto->city);
        $company->setState($dto->state);
        $company->setLogo($dto->logo);

        return $company;
    }

    public function toOutputDTO(Company $company, ?string $logoUrl): CompanyOutputDTO
    {
        return new CompanyOutputDTO(
            id: $company->getId(),
            name: $company->getName(),
            tradingName: $company->getTradingName(),
            registrationNumber: $company->getRegistrationNumber(),
            stateRegistration: $company->getStateRegistration(),
            email: $company->getEmail(),
            phone: $company->getPhone(),
            logo: $logoUrl,
            website: $company->getWebsite(),
            zipCode: $company->getZipCode(),
            street: $company->getStreet(),
            number: $company->getNumber(),
            complement: $company->getComplement(),
            neighborhood: $company->getNeighborhood(),
            city: $company->getCity(),
            state: $company->getState(),
            created_at: $company->getCreatedAt(),
            updated_at: $company->getUpdatedAt(),
        );
    }
}
