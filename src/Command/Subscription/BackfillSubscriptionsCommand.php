<?php

namespace App\Command\Subscription;

use App\Entity\Company;
use App\Repository\CompanyRepository;
use App\Repository\Subscription\SubscriptionRepository;
use App\Service\Subscription\SubscriptionService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:subscription:backfill',
    description: 'Cria uma Subscription em trial para empresas que existiam antes da feature de assinaturas',
)]
class BackfillSubscriptionsCommand extends Command
{
    public function __construct(
        private CompanyRepository $companyRepository,
        private SubscriptionRepository $subscriptionRepository,
        private SubscriptionService $subscriptionService,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $pending = array_filter(
            $this->companyRepository->findAll(),
            fn(Company $company) => $this->subscriptionRepository->findByCompany($company) === null
        );

        $io->progressStart(count($pending));

        foreach ($pending as $company) {
            $this->subscriptionService->startTrial($company);
            $io->progressAdvance();
        }

        $io->progressFinish();
        $io->success(sprintf('%d empresa(s) receberam uma Subscription de backfill.', count($pending)));

        return Command::SUCCESS;
    }
}
