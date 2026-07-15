<?php

namespace App\Command\Subscription;

use App\Repository\Subscription\SubscriptionRepository;
use App\Service\Subscription\SubscriptionService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:subscription:reconcile',
    description: 'Consulta o Asaas e corrige o status local de assinaturas que possam ter perdido um webhook',
)]
class ReconcileSubscriptionsCommand extends Command
{
    public function __construct(
        private SubscriptionRepository $subscriptionRepository,
        private SubscriptionService $subscriptionService,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $subscriptions = $this->subscriptionRepository->createQueryBuilder('s')
            ->andWhere('s.asaasSubscriptionId IS NOT NULL')
            ->getQuery()
            ->getResult();

        $io->progressStart(count($subscriptions));

        foreach ($subscriptions as $subscription) {
            try {
                $this->subscriptionService->reconcile($subscription);
            } catch (\Exception $e) {
                $io->warning(sprintf('Falha ao reconciliar assinatura #%d: %s', $subscription->getId(), $e->getMessage()));
            }

            $io->progressAdvance();
        }

        $io->progressFinish();
        $io->success(sprintf('%d assinatura(s) verificada(s).', count($subscriptions)));

        return Command::SUCCESS;
    }
}
