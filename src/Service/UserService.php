<?php

namespace App\Service;

use App\DTO\Request\User\UserInputDTO;
use App\DTO\Response\User\UserOutputDTO;
use App\Mapper\User\UserMapper;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;

class UserService
{
    public function __construct(
        private UserRepository $userRepository,
        private EntityManagerInterface $entityManager,
        private UserMapper $userMapper,
        private CompanyService $companyService,
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

        $this->companyService->createDraftForUser($user, $dto->name, $dto->email, $dto->phone);

        return $this->userMapper->toOutputDTO($user);
    }

    /**
     * * @return UserOutputDTO[]
     */
    public function getAll(): array
    {
        $users = $this->userRepository->findAll();

        return array_map(
            fn($user) => $this->userMapper->toOutputDTO($user),
            $users
        );
    }

    public function getById(int $userId): UserOutputDTO
    {
        $user = $this->userRepository->findOneBy(['id' => $userId]);

        return $this->userMapper->toOutputDTO($user);
    }
}
