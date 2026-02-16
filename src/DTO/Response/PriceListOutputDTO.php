<?php

namespace App\DTO\Response;

use DateTimeImmutable;

readonly class PriceListOutputDTO
{
    /**
     * @param PriceListItemOutputDTO[] $items
     */
    public function __construct(
        public int $id,
        public int $companyId,
        public string $title,
        public ?string $description,
        public DateTimeImmutable $createdAt,
        public ?DateTimeImmutable $updatedAt,
        public array $items,
    ) {}
}
