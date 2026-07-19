<?php

namespace App\DTO\Request\Subscription;

use Symfony\Component\Validator\Constraints as Assert;

readonly class UpdatePlanInputDTO
{
    public function __construct(
        #[Assert\NotBlank(message: "O nome do plano é obrigatório.")]
        #[Assert\Length(max: 255)]
        public string $name,

        #[Assert\NotBlank(message: "O código do plano é obrigatório.")]
        #[Assert\Length(max: 50)]
        public string $code,

        #[Assert\NotBlank(message: "O preço é obrigatório.")]
        #[Assert\PositiveOrZero]
        public int $priceCents,

        #[Assert\NotBlank(message: "O ciclo de cobrança é obrigatório.")]
        #[Assert\Choice(choices: ['monthly', 'quarterly', 'yearly'], message: "Ciclo de cobrança inválido.")]
        public string $billingCycle,

        #[Assert\PositiveOrZero]
        public int $trialDays,

        public bool $active,

        #[Assert\PositiveOrZero]
        public int $sortOrder,
    ) {}
}
