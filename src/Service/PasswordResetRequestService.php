<?php

namespace App\Service;

use App\Entity\User;
use Symfony\Component\Mime\Email;
use App\Repository\UserRepository;
use App\Entity\PasswordResetRequest;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Mailer\MailerInterface;
use App\Repository\PasswordResetRequestRepository;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class PasswordResetRequestService
{
    public function __construct(
        private string $mailerFrom,
        private MailerInterface $mailer,
        private UserRepository $userRepository,
        private EntityManagerInterface $entityManager,
        private PasswordResetRequestRepository $repository,
        private UserPasswordHasherInterface $passwordHasher,
    ) {}

    public function createRequest(string $email): ?PasswordResetRequest
    {
        $user = $this->userRepository->findOneBy(['email' => $email]);

        if (!$user) {
            return null;
        }

        $this->invalidateOldRequests($user);

        $request = new PasswordResetRequest();
        $request->setUser($user);
        $request->setCode((string) random_int(100000, 999999));
        $request->setCreatedAt(new \DateTimeImmutable());
        $request->setExpiresAt(new \DateTimeImmutable('+15 minutes'));
        $request->setIsUsed(false);

        $this->entityManager->persist($request);
        $this->entityManager->flush();

        $this->sendEmail($user->getEmail(), $request->getCode());

        return $request;
    }

    public function validateCode(string $email, string $code): bool
    {
        $user = $this->userRepository->findOneBy(['email' => $email]);

        if (!$user) {
            return false;
        }

        $request = $this->repository->findOneBy([
            'user' => $user,
            'code' => $code,
            'isUsed' => false
        ]);

        if (!$request || $request->getExpiresAt() < new \DateTimeImmutable()) {
            return false;
        }

        return true;
    }

    public function resetPassword(string $email, string $code, string $newPlainPassword): bool
    {
        $user = $this->userRepository->findOneBy(['email' => $email]);

        if (!$user || !$this->validateCode($email, $code)) {
            return false;
        }

        $hashedPassword = $this->passwordHasher->hashPassword($user, $newPlainPassword);
        $user->setPassword($hashedPassword);

        $this->markAsUsed($email, $code);

        return true;
    }

    public function markAsUsed(string $email, string $code): void
    {
        $user = $this->userRepository->findOneBy(['email' => $email]);
        $request = $this->repository->findOneBy([
            'user' => $user,
            'code' => $code,
            'isUsed' => false
        ]);

        if ($request) {
            $request->setIsUsed(true);
            $this->entityManager->flush();
        }
    }

    private function sendEmail(string $to, string $code): void
    {
        $email = (new Email())
            ->from($this->mailerFrom)
            ->to($to)
            ->subject('Seu código de recuperação')
            ->html("<p>Seu código de recuperação é: <strong>$code</strong>. Ele expira em 15 minutos.</p>");

        $this->mailer->send($email);
    }

    private function invalidateOldRequests(User $user): void
    {
        $oldRequests = $this->repository->findBy([
            'user' => $user,
            'isUsed' => false
        ]);

        foreach ($oldRequests as $old) {
            $old->setIsUsed(true);
        }
    }
}
