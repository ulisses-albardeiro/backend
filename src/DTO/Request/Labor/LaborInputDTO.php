<?php

namespace App\DTO\Request\Labor;

use App\Enum\Labor\LaborStatus;
use App\Enum\Labor\LaborUnit;
use Symfony\Component\Validator\Constraints as Assert;

readonly class LaborInputDTO
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Length(max: 255)]
        public string $name,

        #[Assert\NotBlank]
        public int $categoryId,

        public ?string $description,

        #[Assert\NotBlank]
        #[Assert\PositiveOrZero]
        public int $salePrice,

        #[Assert\NotBlank]
        public LaborUnit $unit,

        #[Assert\NotBlank]
        public LaborStatus $status,
    ) {}
}
