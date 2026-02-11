<?php

namespace App\Dto\Response;

readonly class CustomerOutputDTO
{
    public function __construct(
        public int $id,
        public string $type,
        public string $typeLabel,
        public string $name,
        public ?string $tradingName,
        public ?string $document,
        public ?string $stateRegistration,
        public ?string $email,
        public ?string $phone,
        public ?string $zipCode,
        public ?string $street,
        public ?string $number,
        public ?string $complement,
        public ?string $neighborhood,
        public ?string $city,
        public ?string $state,
        public bool $status,
        public ?string $notes,
        public string $createdAt,
    ) {}
}
