<?php

namespace App\Service;

use App\Entity\Company;
use App\Entity\Customer;
use App\Mapper\CustomerMapper;
use App\DTO\Request\CustomerInputDTO;
use App\Repository\CustomerRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class CustomerService
{
    public function __construct(
        private CustomerMapper $mapper,
        private EntityManagerInterface $em,
        private CustomerRepository $repository,
    ) {}

    public function listAllByCompany(Company $company): array
    {
        $customers = $this->repository->findBy(
            ['company' => $company],
            ['name' => 'ASC']
        );

        return array_map(fn($c) => $this->mapper->toOutputDTO($c), $customers);
    }

    public function getByIdAndCompany(int $id, Company $company): Customer
    {
        $customer = $this->repository->findOneBy(['id' => $id, 'company' => $company]);

        if (!$customer) {
            throw new NotFoundHttpException('CUSTOMER_NOT_FOUND');
        }

        return $customer;
    }

    public function create(CustomerInputDTO $dto, Company $company): Customer
    {
        $customer = $this->mapper->toEntity($dto, $company);
        $this->em->persist($customer);
        $this->em->flush();

        return $customer;
    }

    public function update(int $id, CustomerInputDTO $dto, Company $company): Customer
    {
        $customer = $this->getByIdAndCompany($id, $company);

        $this->mapper->toEntity($dto, $company, $customer);

        $this->em->flush();

        return $customer;
    }

    public function delete(int $id, Company $company): void
    {
        $customer = $this->getByIdAndCompany($id, $company);

        $this->em->remove($customer);
        $this->em->flush();
    }
}
