<?php

namespace App\DTO\Request;

use App\Enum\TransactionType;
use Symfony\Component\Validator\Constraints as Assert;

readonly class CategoryInputDTO
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Length(max: 255)]
        public string $name,

        #[Assert\NotBlank]
        public TransactionType $type,

        public ?int $parentId = null,

        #[Assert\Length(max: 7)]
        public ?string $color = null,

        #[Assert\Length(max: 50)]
        public ?string $icon = null,

        #[Assert\NotNull]
        public bool $status = true,
    ) {}
}