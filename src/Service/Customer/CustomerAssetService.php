<?php

namespace App\Service\Customer;

use App\DTO\Request\Customer\CustomerAssetInputDTO;
use App\Entity\Company;
use App\Mapper\Customer\CustomerAssetMapper;
use App\DTO\Response\Customer\CustomerAssetOutputDTO;
use App\Repository\Customer\CustomerAssetRepository;
use App\Repository\CustomerRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class CustomerAssetService
{
    public function __construct(
        private CustomerAssetMapper $mapper,
        private EntityManagerInterface $em,
        private CustomerAssetRepository $repository,
        private CustomerRepository $customerRepository,
    ) {}

    public function listAllByCompany(Company $company): array
    {
        $customers = $this->repository->findBy(
            ['company' => $company],
            ['name' => 'ASC']
        );

        return array_map(fn($c) => $this->mapper->toOutputDTO($c), $customers);
    }

    public function getByIdAndCompany(int $id, Company $company): CustomerAssetOutputDTO
    {
        $customerAsset = $this->repository->findOneBy(['id' => $id, 'company' => $company]);

        if (!$customerAsset) {
            throw new NotFoundHttpException('CUSTOMER_NOT_FOUND');
        }

        return $this->mapper->toOutputDTO($customerAsset);
    }

    public function create(CustomerAssetInputDTO $dto, Company $company): CustomerAssetOutputDTO
    {
        $customer = $this->customerRepository->findOneBy(['id' => $dto->customerId, 'company' => $company]);
        $customerAsset = $this->mapper->toEntity($dto, $company, $customer);
        $this->em->persist($customerAsset);
        $this->em->flush();

        return $this->mapper->toOutputDTO($customerAsset);
    }

    public function update(int $id, CustomerAssetInputDTO $dto, Company $company): CustomerAssetOutputDTO
    {
        $customerAsset = $this->repository->findOneBy(['id' => $id, 'company' => $company]);
        $customer = $this->customerRepository->findOneBy(['id' => $dto->customerId, 'company' => $company]);
        $this->mapper->toEntity($dto, $company, $customer);

        $this->em->flush();

        return $this->mapper->toOutputDTO($customerAsset);
    }

    public function delete(int $id, Company $company): void
    {
        $customerAsset = $this->repository->findOneBy(['id' => $id, 'company' => $company]);

        $this->em->remove($customerAsset);
        $this->em->flush();
    }
}
