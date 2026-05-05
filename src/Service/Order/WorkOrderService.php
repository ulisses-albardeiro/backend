<?php

namespace App\Service\Order;

use App\Entity\Company;
use App\Entity\Order\WorkOrder;
use App\Enum\TransactionStatus;
use App\Enum\TransactionType;
use App\Mapper\Order\WorkOrderMapper;
use App\Mapper\TransactionMapper;
use App\DTO\Request\Order\WorkOrderInputDTO;
use App\DTO\Request\TransactionInputDTO;
use App\DTO\Response\Order\WorkOrderOutputDTO;
use App\Entity\Category;
use App\Repository\CategoryRepository;
use App\Repository\Order\WorkOrderRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class WorkOrderService
{
    public function __construct(
        private WorkOrderMapper $mapper,
        private TransactionMapper $transactionMapper,
        private EntityManagerInterface $em,
        private WorkOrderRepository $repository,
        private CategoryRepository $categoryRepo,
    ) {}

    public function create(WorkOrderInputDTO $dto, Company $company): WorkOrderOutputDTO
    {
        $workOrder = $this->mapper->toEntity($dto, $company);

        $transaction = $this->createAutomaticTransaction($workOrder, $company);

        $workOrder->setTransaction($transaction);

        $this->em->persist($workOrder);
        $this->em->flush();

        return $this->mapper->toOutputDTO($workOrder);
    }

    public function update(int $id, WorkOrderInputDTO $dto, Company $company): WorkOrderOutputDTO
    {
        $workOrder = $this->repository->findOneBy(['id' => $id, 'company' => $company]);

        if (!$workOrder) {
            throw new NotFoundHttpException('WORK_ORDER_NOT_FOUND');
        }

        $this->mapper->toEntity($dto, $company, $workOrder);

        $transaction = $workOrder->getTransaction();
        $transaction->setAmount($workOrder->getTotalAmount());
        $transaction->setDescription("OS: " . $workOrder->getCode() . " - " . $workOrder->getTitle());

        $this->em->flush();

        return $this->mapper->toOutputDTO($workOrder);
    }

    /**
     * Lógica para criar a transação financeira automática
     */
    private function createAutomaticTransaction(WorkOrder $workOrder, Company $company)
    {
        // 1. Tenta buscar a categoria "Serviços"
        $category = $this->categoryRepo->findOneBy([
            'company' => $company,
            'name' => 'Serviços'
        ]);

        if (!$category) {
            $category = new Category();
            $category->setName('Serviços');
            $category->setCompany($company);
            $category->setColor('#3b82f6');
            $category->setType(TransactionType::INCOME);
            $category->setStatus(true);

            $this->em->persist($category);
            $this->em->flush();
        }


        $tDto = new TransactionInputDTO();
        $tDto->description = "OS: " . $workOrder->getCode() . " - " . $workOrder->getTitle();
        $tDto->amount = $workOrder->getTotalAmount();
        $tDto->date = (new \DateTimeImmutable())->format('Y-m-d');
        $tDto->type = TransactionType::INCOME->value;
        $tDto->status = TransactionStatus::PENDING->value;
        $tDto->categoryId = $category->getId();
        $tDto->customerId = $workOrder->getCustomer()->getId();

        return $this->transactionMapper->toEntity($tDto, $company, $category, $workOrder->getCustomer());
    }

    public function listAllByCompany(Company $company): array
    {
        $orders = $this->repository->findBy(['company' => $company], ['createdAt' => 'DESC']);
        return array_map(fn($o) => $this->mapper->toOutputDTO($o), $orders);
    }

    public function delete(int $id, Company $company): void
    {
        $workOrder = $this->repository->findOneBy(['id' => $id, 'company' => $company]);
        if ($workOrder) {
            $this->em->remove($workOrder);
            $this->em->flush();
        }
    }

    public function getByIdAndCompany(int $id, Company $company): ?WorkOrderOutputDTO
    {
        $workOrder = $this->repository->findOneBy([
            'id' => $id,
            'company' => $company
        ]);

        if (!$workOrder) {
            return null;
        }

        return $this->mapper->toOutputDTO($workOrder);
    }
}
