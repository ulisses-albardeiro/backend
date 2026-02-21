<?php

namespace App\Controller;

use App\Repository\CustomerRepository;
use App\Repository\UserRepository;
use App\Repository\QuoteRepository;
use App\Repository\TransactionRepository;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/api/admin/', name: 'api_admin_', format: 'json')]
final class AdminController extends AbstractController
{
    public function __construct(
        private UserRepository $userRepository,
        private QuoteRepository $quoteRepository,
        private CustomerRepository $customerRepository,
        private TransactionRepository $transactionRepository,
    ) {}

    #[Route('dashboard', name: 'dashboard', methods: ['GET'])]
    public function index(): JsonResponse
    {
        return $this->json([
            "users" => $this->userRepository->findAllWithCompany(),
            "quantityQuotes" => $this->quoteRepository->countAll(),
            "quantityCustomers" => $this->customerRepository->countAll(),
            "quantityTransactions" => $this->transactionRepository->countAll(),
        ]);
    }
}
