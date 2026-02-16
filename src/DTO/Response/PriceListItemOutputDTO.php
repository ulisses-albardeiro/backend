<?php

namespace App\DTO\Response;

readonly class PriceListItemOutputDTO
{
    public function __construct(
        public int $id,
        public string $name,
        public string $quantity,
        public string $unit,
        public string $unitLabel,
    ) {}
}
