<?php

namespace App\DTO\Request;

use Symfony\Component\Validator\Constraints as Assert;

readonly class TechnicianInputDTO
{
    public function __construct(
        #[Assert\NotBlank(message: 'O nome da assinatura é obrigatório.')]
        #[Assert\Length(max: 255, maxMessage: 'O nome da assinatura deve ter no máximo {{ limit }} caracteres.')]
        public string $name,
    ) {}
}
