<?php

namespace App\DTO\Request\Subscription;

use Symfony\Component\Validator\Constraints as Assert;

readonly class ChoosePlanInputDTO
{
    public function __construct(
        #[Assert\NotBlank(message: "O plano é obrigatório.")]
        #[Assert\Positive]
        public int $planId,

        #[Assert\NotBlank(message: "A forma de pagamento é obrigatória.")]
        #[Assert\Choice(choices: ['credit_card', 'pix', 'boleto'], message: "Forma de pagamento inválida.")]
        public string $billingType,

        // CPF ou CNPJ do responsável pelo pagamento — obrigatório apenas quando a empresa
        // ainda não tem CNPJ cadastrado (Company::registrationNumber)
        public ?string $holderCpfCnpj = null,

        // Obrigatórios apenas quando billingType = credit_card
        public ?string $cardHolderName = null,
        public ?string $cardNumber = null,
        public ?string $cardExpiryMonth = null,
        public ?string $cardExpiryYear = null,
        public ?string $cardCcv = null,
        public ?string $holderPostalCode = null,
        public ?string $holderAddressNumber = null,
        public ?string $holderPhone = null,
    ) {}
}
