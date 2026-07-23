<?php

namespace App\DTO\Response;

use DateTimeImmutable;

readonly class TechnicianOutputDTO
{
    public function __construct(
        public int $id,
        public string $name,
        public ?string $signatureUrl,
        public DateTimeImmutable $createdAt,
        public ?DateTimeImmutable $updatedAt,
    ) {}
}
