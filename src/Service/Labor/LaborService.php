<?php

namespace App\Service\Labor;

use App\DTO\Request\Labor\LaborInputDTO;
use App\DTO\Response\Labor\LaborOutputDTO;
use App\Entity\Company;
use App\Enum\Labor\LaborStatus;
use App\Enum\Labor\LaborUnit;
use App\Mapper\Labor\LaborMapper;
use App\Repository\Labor\LaborCategoryRepository;
use App\Repository\Labor\LaborRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class LaborService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private LaborRepository $laborRepository,
        private LaborMapper $laborMapper,
        private LaborCategoryRepository $categoryRepository,
    ) {}

    public function create(LaborInputDTO $dto, Company $company): LaborOutputDTO
    {
        $labor = $this->laborMapper->toEntity($dto);
        $labor->setCompany($company);

        $this->entityManager->persist($labor);


        $this->entityManager->flush();

        return $this->laborMapper->toOutput($labor);
    }

    public function update(int $id, LaborInputDTO $dto, Company $company): LaborOutputDTO
    {
        $labor = $this->laborRepository->findOneBy(['id' => $id, 'company' => $company]);

        if (!$labor) {
            throw new NotFoundHttpException("Serviço não encontrado.");
        }

        $labor = $this->laborMapper->toEntity($dto, $labor);

        $this->entityManager->flush();

        return $this->laborMapper->toOutput($labor);
    }

    public function listAll(Company $company): array
    {
        $labors = $this->laborRepository->findBy([
            'company' => $company,
        ]);

        return array_map(
            fn($l) => $this->laborMapper->toOutput($l),
            $labors
        );
    }

    public function listActive(Company $company): array
    {
        $labors = $this->laborRepository->findBy([
            'company' => $company,
            'status' => LaborStatus::ACTIVE
        ]);

        return array_map(
            fn($l) => $this->laborMapper->toOutput($l),
            $labors
        );
    }

    public function getById(int $id, Company $company): LaborOutputDTO
    {
        $labor = $this->laborRepository->findOneBy(['id' => $id, 'company' => $company]);

        if (!$labor) {
            throw new NotFoundHttpException('LABOR_NOT_FOUND');
        }

        return $this->laborMapper->toOutput($labor);
    }

    public function delete(int $id, Company $company): void
    {
        $labor = $this->laborRepository->findOneBy(['id' => $id, 'company' => $company]);

        if (!$labor) {
            throw new NotFoundHttpException('LABOR_NOT_FOUND');
        }

        try {
            $this->entityManager->remove($labor);
            $this->entityManager->flush();
        } catch (\Exception $e) {
            // Se houver vínculo em Ordens de Serviço, o SQL vai barrar. 
            // Nesse caso, apenas inativamos.
            $labor->setStatus(LaborStatus::INACTIVE);
            $this->entityManager->flush();
        }
    }

    /**
     * Cria serviços padrão associados às categorias recém-criadas.
     */
    public function createDefaultLabors(Company $company): void
    {
        $defaults = [
            'Serviço de Instalação'  => 'Instalação',
            'Serviço de Construção'  => 'Construção',
            'Serviço de Manutenção'  => 'Manutenção',
            'Consultoria Técnica'    => 'Consultoria',
            'Serviço de Medição'     => 'Medição',
        ];

        foreach ($defaults as $laborName => $categoryName) {
            $category = $this->categoryRepository->findOneBy([
                'name' => $categoryName,
                'company' => $company
            ]);

            if (!$category) {
                continue;
            }

            $dto = new LaborInputDTO(
                name: $laborName,
                categoryId: $category->getId(),
                description: "Serviço padrão de $laborName",
                salePrice: 0,
                unit: LaborUnit::UNIDADE,
                status: LaborStatus::ACTIVE
            );

            $labor = $this->laborMapper->toEntity($dto);
            $labor->setCompany($company);

            $this->entityManager->persist($labor);
        }

        $this->entityManager->flush();
    }
}
