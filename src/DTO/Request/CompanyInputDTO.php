<?php

namespace App\DTO\Request;

use Symfony\Component\Validator\Constraints as Assert;

readonly class CompanyInputDTO
{
    public function __construct(
        
        #[Assert\NotBlank(message: "O nome empresarial é obrigatório.")]
        #[Assert\Length(max: 255)]
        public string $name,

        #[Assert\Length(max: 255)]
        public ?string $tradingName = null,

        #[Assert\Length(min: 14, max: 14)]
        public ?string $registrationNumber = null,

        #[Assert\Length(max: 50)]
        public ?string $stateRegistration = null,

        #[Assert\NotBlank(message: "O e-mail é obrigatório.")]
        #[Assert\Email(message: "O e-mail informado é inválido.")]
        #[Assert\Length(max: 50)]
        public string $email,

        #[Assert\NotBlank(message: "O telefone é obrigatório.")]
        #[Assert\Length(max: 15)]
        public string $phone,

        public ?string $website = null,

        #[Assert\Length(max: 10)]
        public ?string $zipCode = null,

        public ?string $street = null,
        public ?string $number = null,
        public ?string $complement = null,
        public ?string $neighborhood = null,

        public ?string $city = null,
        
        #[Assert\Length(max: 50)]
        public ?string $state = null,

        public ?string $logo = null,
    ) {}
}
