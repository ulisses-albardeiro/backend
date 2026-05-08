<?php

namespace App\DTO\Response\Customer;

use DateTimeImmutable;

readonly class CustomerAssetOutputDTO
{
    public function __construct(
        public int $id,
        public int $customerId,
        public string $customerName,
        public string $name,
        public DateTimeImmutable $createdAt,
    ) {}
}
