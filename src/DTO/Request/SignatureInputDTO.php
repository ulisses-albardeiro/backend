<?php

namespace App\DTO\Request;

use Symfony\Component\Validator\Constraints as Assert;

readonly class SignatureInputDTO
{
    public function __construct(
        #[Assert\NotBlank(message: 'O técnico da assinatura é obrigatório.')]
        #[Assert\Positive]
        public int $technicianId,
    ) {}
}
