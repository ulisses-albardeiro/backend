<?php

namespace App\Mapper;

use App\Entity\Company;
use App\Entity\Technician;
use App\DTO\Request\TechnicianInputDTO;
use App\DTO\Response\TechnicianOutputDTO;

class TechnicianMapper
{
    public function toEntity(TechnicianInputDTO $dto, Company $company, ?Technician $technician = null): Technician
    {
        $technician ??= new Technician();

        $technician->setCompany($company);
        $technician->setName($dto->name);

        return $technician;
    }

    public function toOutputDTO(Technician $technician, ?string $signatureUrl): TechnicianOutputDTO
    {
        return new TechnicianOutputDTO(
            id: $technician->getId(),
            name: $technician->getName(),
            signatureUrl: $signatureUrl,
            createdAt: $technician->getCreatedAt(),
            updatedAt: $technician->getUpdatedAt(),
        );
    }
}
