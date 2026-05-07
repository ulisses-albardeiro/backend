<?php

namespace App\Mapper\User;

use App\Entity\User;
use App\DTO\Request\User\UserInputDTO;
use App\DTO\Response\User\UserOutputDTO;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserMapper
{
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher
    ) {}

    public function toEntity(UserInputDTO $dto, ?User $user = null): User
    {
        $user ??= new User();

        $user->setName($dto->name);
        $user->setEmail($dto->email);
        $user->setRoles(['ROLE_USER']);
        $user->setPhone($dto->phone);
        $user->setGoogleId($dto->googleId);

        if ($dto->password) {
            $hashedPassword = $this->passwordHasher->hashPassword($user, $dto->password);
            $user->setPassword($hashedPassword);
        }

        return $user;
    }

    public function toOutputDTO(User $user): UserOutputDTO
    {
        return new UserOutputDTO(
            id: $user->getId(),
            name: $user->getName(),
            email: $user->getEmail(),
            roles: $user->getRoles(),
            phone: $user->getPhone(),
            googleId: $user->getGoogleId(),
            companyId: $user->getCompany()?->getId(),
            companyName: $user->getCompany()?->getName(),
            createdAt: $user->getCreatedAt()?->format(\DateTimeInterface::ATOM),
            updatedAt: $user->getUpdatedAt()?->format(\DateTimeInterface::ATOM)
        );
    }
}
