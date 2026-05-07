<?php

namespace App\DTO\Request\User;

use Symfony\Component\Validator\Constraints as Assert;

readonly class UserInputDTO
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Length(max: 255)]
        public string $name,

        #[Assert\NotBlank]
        #[Assert\Email]
        #[Assert\Length(max: 180)]
        public string $email,       

        #[Assert\Length(min: 8, max: 255)]
        public ?string $password = null,

        #[Assert\Length(min: 10, max: 11)]
        #[Assert\Regex(pattern: '/^\d+$/', message: 'O telefone deve conter apenas números.')]
        public ?string $phone = null,

        #[Assert\Length(max: 255)]
        public ?string $googleId = null,
    ) {}
}