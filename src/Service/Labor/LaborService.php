<?php

namespace App\Service\Labor;

use App\DTO\Request\Labor\LaborInputDTO;
use App\DTO\Response\Labor\LaborOutputDTO;
use App\Entity\Company;
use App\Enum\Labor\LaborStatus;
use App\Mapper\Labor\LaborMapper;
use App\Repository\Labor\LaborRepository;
use App\Service\FileService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class LaborService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private LaborRepository $laborRepository,
        private LaborMapper $laborMapper,
        private FileService $fileService
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
}