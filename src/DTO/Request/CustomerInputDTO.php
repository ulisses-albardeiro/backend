<?php

namespace App\DTO\Request;

use App\Enum\CustomerType;
use Symfony\Component\Validator\Constraints as Assert;

readonly class CustomerInputDTO
{
    public function __construct(
        #[Assert\NotBlank]
        public CustomerType $type,

        #[Assert\NotBlank]
        #[Assert\Length(max: 255)]
        public string $name,

        #[Assert\Length(max: 255)]
        public ?string $tradingName = null,

        #[Assert\Length(min: 11, max: 14)]
        public ?string $document = null,

        #[Assert\Length(max: 20)]
        public ?string $stateRegistration = null,

        #[Assert\Email]
        #[Assert\Length(max: 180)]
        public ?string $email = null,

        #[Assert\Length(max: 20)]
        public ?string $phone = null,

        #[Assert\Length(max: 10)]
        public ?string $zipCode = null,

        #[Assert\Length(max: 255)]
        public ?string $street = null,

        #[Assert\Length(max: 20)]
        public ?string $number = null,

        #[Assert\Length(max: 255)]
        public ?string $complement = null,

        #[Assert\Length(max: 100)]
        public ?string $neighborhood = null,

        #[Assert\Length(max: 100)]
        public ?string $city = null,

        #[Assert\Length(exactly: 2)]
        public ?string $state = null,

        #[Assert\NotNull]
        public bool $status = true,

        public ?string $notes = null,
    ) {}
}
