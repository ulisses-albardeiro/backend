<?php

namespace App\Service;

use App\Entity\User;
use Symfony\Component\Mime\Email;
use App\Repository\UserRepository;
use App\Entity\PasswordResetRequest;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Mailer\MailerInterface;
use App\Repository\PasswordResetRequestRepository;

class PasswordResetRequestService
{
    public function __construct(
        private string $mailerFrom,
        private MailerInterface $mailer,
        private UserRepository $userRepository,
        private EntityManagerInterface $entityManager,
        private PasswordResetRequestRepository $repository,
    ) {}

    /**
     * Generate a new recovery request and send the email.
     */
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

    /**
     * Checks if the submitted code is valid and has not expired.
     */
    public function validateCode(string $email, string $code): bool
    {
        $user = $this->userRepository->findOneBy(['email' => $email]);

        if (!$user) {
            return false;
        }

        $request = $this->repository->findOneBy([
            'requestedBy' => $user,
            'code' => $code,
            'is_used' => false
        ]);

        if (!$request) {
            return false;
        }

        if ($request->getExpiresAt() < new \DateTimeImmutable()) {
            return false;
        }

        return true;
    }

    /**
     * Mark the code as used.
     */
    public function markAsUsed(string $email, string $code): void
    {
        $user = $this->userRepository->findOneBy(['email' => $email]);
        $request = $this->repository->findOneBy([
            'requestedBy' => $user,
            'code' => $code
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
            'requestedBy' => $user, 
            'is_used' => false
        ]);

        foreach ($oldRequests as $old) {
            $old->setIsUsed(true);
        }
    }
}