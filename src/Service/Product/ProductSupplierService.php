<?php

namespace App\Service\Product;

use App\DTO\Request\Product\SupplierInputDTO;
use App\DTO\Response\Product\SupplierOutputDTO;
use App\Entity\Company;
use App\Mapper\Product\SupplierMapper;
use App\Repository\Product\SupplierRepository;
use Doctrine\ORM\EntityManagerInterface;

class ProductSupplierService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private SupplierRepository $repository,
        private SupplierMapper $mapper
    ) {}

    public function create(SupplierInputDTO $dto, Company $company): SupplierOutputDTO
    {
        $supplier = $this->mapper->toEntity($dto);
        $supplier->setCompany($company);

        $this->entityManager->persist($supplier);
        $this->entityManager->flush();

        return $this->mapper->toOutput($supplier);
    }

    public function update(int $id, SupplierInputDTO $dto, Company $company): SupplierOutputDTO
    {
        $supplier = $this->repository->findOneBy(['id' => $id, 'company' => $company]);

        if (!$supplier) {
            throw new \Exception("Fornecedor não encontrado ou você não tem permissão para editá-lo.");
        }

        $supplier = $this->mapper->toEntity($dto, $supplier);
        $this->entityManager->flush();

        return $this->mapper->toOutput($supplier);
    }

    public function delete(int $id, Company $company): void
    {
        $supplier = $this->repository->findOneBy(['id' => $id, 'company' => $company]);

        if (!$supplier) {
            throw new \Exception("Fornecedor não encontrado.");
        }

        // [TODO] Verificar se existem produtos vinculados antes de deletar
        // Se houver, você pode lançar uma exceção ou apenas desativar (soft delete)
        $this->entityManager->remove($supplier);
        $this->entityManager->flush();
    }

    /**
     * @return SupplierOutputDTO[]
     */
    public function listAll(Company $company): array
    {
        $suppliers = $this->repository->findBy(['company' => $company], ['name' => 'ASC']);
        
        return array_map(
            fn($supplier) => $this->mapper->toOutput($supplier),
            $suppliers
        );
    }
}
