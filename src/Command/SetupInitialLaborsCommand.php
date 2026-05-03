<?php

namespace App\Command;

use App\Repository\CompanyRepository;
use App\Service\Labor\LaborCategoryService;
use App\Service\Labor\LaborService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'app:setup-initial-labors', description: 'Popula categorias e serviços para empresas antigas')]
class SetupInitialLaborsCommand extends Command
{
    public function __construct(
        private CompanyRepository $companyRepository,
        private LaborCategoryService $categoryService,
        private LaborService $laborService
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $companies = $this->companyRepository->findAll();

        $io->progressStart(count($companies));

        foreach ($companies as $company) {
            if (empty($this->categoryService->listAllByCompany($company))) {
                $this->categoryService->createDefaultCategories($company);
                $this->laborService->createDefaultLabors($company);
            }
            
            $io->progressAdvance();
        }

        $io->progressFinish();
        $io->success('Setup concluído para todas as empresas!');

        return Command::SUCCESS;
    }
}
