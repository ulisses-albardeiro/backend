<?php

namespace App\DTO\Response\User;

readonly class UserOutputDTO
{
    public function __construct(
        public int $id,
        public string $name,
        public string $email,
        /** @var list<string> */
        public array $roles,
        public ?string $phone,
        public ?string $googleId,
        public ?int $companyId,
        public ?string $companyName,
        public string $createdAt,
        public ?string $updatedAt
    ) {}
}