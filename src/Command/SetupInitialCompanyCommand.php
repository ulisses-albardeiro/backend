<?php

namespace App\Command;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\CompanyService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'app:setup-initial-company', description: 'Cria uma Company mínima para usuários legados sem empresa vinculada')]
class SetupInitialCompanyCommand extends Command
{
    public function __construct(
        private UserRepository $userRepository,
        private CompanyService $companyService,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $pending = array_filter(
            $this->userRepository->findAll(),
            fn(User $user) => $user->getCompany() === null
        );

        $io->progressStart(count($pending));

        foreach ($pending as $user) {
            $this->companyService->createDraftForUser(
                $user,
                $user->getName(),
                $user->getEmail(),
                $user->getPhone()
            );

            $io->progressAdvance();
        }

        $io->progressFinish();
        $io->success(sprintf('%d usuário(s) receberam uma Company de backfill.', count($pending)));

        return Command::SUCCESS;
    }
}
