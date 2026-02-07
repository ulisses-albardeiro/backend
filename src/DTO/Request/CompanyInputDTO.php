<?php

namespace App\DTO\Request;

use Symfony\Component\Validator\Constraints as Assert;

readonly class CompanyInputDTO
{
    public function __construct(
        #[Assert\Length(max: 255)]
        public ?string $name,

        #[Assert\NotBlank]
        #[Assert\Length(max: 255)]
        public string $tradingName,

        #[Assert\Length(min: 14, max: 14)]
        public ?string $registrationNumber,

        #[Assert\NotBlank]
        #[Assert\Length(max: 50)]
        public string $stateRegistration,

        #[Assert\Email]
        #[Assert\Length(max: 50)]
        public ?string $email,

        #[Assert\Length(max: 15)]
        public ?string $phone,

        public ?string $website,

        #[Assert\Length(max: 10)]
        public ?string $zipCode,

        public ?string $street,
        public ?string $number,
        public ?string $complement,
        public ?string $neighborhood,

        #[Assert\NotBlank]
        public string $city,

        #[Assert\NotBlank]
        #[Assert\Length(max: 50)]
        public string $state,

        public ?string $logo = null,
    ) {}
}
