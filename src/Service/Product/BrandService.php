<?php

namespace App\Service\Product;

use App\DTO\Request\Product\BrandInputDTO;
use App\DTO\Response\Product\BrandOutputDTO;
use App\Entity\Company;
use App\Mapper\Product\BrandMapper;
use App\Repository\Product\BrandRepository;
use Doctrine\ORM\EntityManagerInterface;

class BrandService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private BrandMapper $mapper,
        private BrandRepository $repository
    ) {}

    public function create(BrandInputDTO $dto, Company $company): BrandOutputDTO
    {
        $brand = $this->mapper->toEntity($dto);
        $brand->setCompany($company);

        $this->entityManager->persist($brand);
        $this->entityManager->flush();

        return $this->mapper->toOutput($brand);
    }

    public function listAll(Company $company): array
    {
        $products = $this->repository->findBy(['company' => $company]);
        return array_map(fn($p) => $this->mapper->toOutput($p), $products);
    }
}
