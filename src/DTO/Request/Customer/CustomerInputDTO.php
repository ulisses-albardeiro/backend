<?php

namespace App\DTO\Request\Customer;

use App\Enum\CustomerType;
use App\Validator\DocumentValidator;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

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

    #[Assert\Callback]
    public function validateDocument(ExecutionContextInterface $context): void
    {
        if (!DocumentValidator::isValidForType($this->document, $this->type)) {
            $context->buildViolation('CPF/CNPJ inválido para o tipo de pessoa selecionado.')
                ->atPath('document')
                ->addViolation();
        }
    }
}
