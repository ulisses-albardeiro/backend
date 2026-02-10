<?php

namespace App\DTO\Response;

use DateTimeImmutable;

readonly class CompanyOutputDTO
{
    public function __construct(
        public int $id,
        public ?string $name,
        public string $tradingName,
        public ?string $registrationNumber,
        public string $stateRegistration,
        public ?string $email,
        public ?string $phone,
        public ?string $logo,
        public ?string $website,
        public ?string $zipCode,
        public ?string $street,
        public ?string $number,
        public ?string $complement,
        public ?string $neighborhood,
        public string $city,
        public string $state,
        public DateTimeImmutable $created_at,
        public ?DateTimeImmutable $updated_at,
    ) {}
}
