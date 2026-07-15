<?php

namespace App\Command\Subscription;

use App\Enum\Subscription\SubscriptionStatus;
use App\Repository\Subscription\SubscriptionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:subscription:expire-trials',
    description: 'Marca como EXPIRED as assinaturas em TRIALING cujo trial já venceu',
)]
class ExpireTrialsCommand extends Command
{
    public function __construct(
        private SubscriptionRepository $subscriptionRepository,
        private EntityManagerInterface $entityManager,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $expired = $this->subscriptionRepository->createQueryBuilder('s')
            ->andWhere('s.status = :status')
            ->andWhere('s.trialEndsAt < :now')
            ->setParameter('status', SubscriptionStatus::TRIALING)
            ->setParameter('now', new \DateTimeImmutable())
            ->getQuery()
            ->getResult();

        foreach ($expired as $subscription) {
            $subscription->setStatus(SubscriptionStatus::EXPIRED);
        }

        $this->entityManager->flush();

        $io->success(sprintf('%d assinatura(s) em trial expirado marcada(s) como EXPIRED.', count($expired)));

        return Command::SUCCESS;
    }
}
