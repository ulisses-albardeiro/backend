<?php

namespace App\Service;

use Exception;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserService
{
    public function __construct(
        private UserRepository $userRepository,
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $passwordHasher,
    ) {}

    public function create(array $data): User
    {
        $existingUser = $this->userRepository->findOneBy(['email' => $data['email']]);

        if ($existingUser) {
            throw new Exception("Este e-mail já está em uso.");
        }

        $user = new User();
        $user->setEmail($data['email']);
        $user->setPhone($data['phone']);

        $hashedPassword = $this->passwordHasher->hashPassword($user, $data['password']);
        $user->setPassword($hashedPassword);
        $user->setRoles(['ROLE_USER']);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }
}
