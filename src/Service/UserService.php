<?php

namespace App\Service;

use App\DTO\Request\User\UserInputDTO;
use App\DTO\Response\User\UserOutputDTO;
use App\Mapper\UserMapper;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserService
{
    public function __construct(
        private UserRepository $userRepository,
        private EntityManagerInterface $entityManager,
        private UserMapper $userMapper,
    ) {}

    public function create(UserInputDTO $dto): UserOutputDTO
    {
        $existingUser = $this->userRepository->findOneBy(['email' => $dto->email]);

        if ($existingUser) {
            throw new \InvalidArgumentException("EMAIL_ALREADY_EXISTS");
        }

        if (strlen($dto->name) > 255) {
            throw new \InvalidArgumentException("NAME_TOO_LONG");
        }

        $user = $this->userMapper->toEntity($dto);
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $this->userMapper->toOutputDTO($user);
    }
}
