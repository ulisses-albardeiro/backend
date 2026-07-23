<?php

namespace App\Service;

use App\Entity\Company;
use App\Entity\Technician;
use App\Mapper\TechnicianMapper;
use App\DTO\Request\TechnicianInputDTO;
use App\DTO\Response\TechnicianOutputDTO;
use App\Repository\TechnicianRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class TechnicianService
{
    public function __construct(
        private TechnicianMapper $mapper,
        private EntityManagerInterface $em,
        private TechnicianRepository $repository,
        private FileService $fileService,
    ) {}

    public function listAllByCompany(Company $company): array
    {
        $technicians = $this->repository->findBy(
            ['company' => $company],
            ['name' => 'ASC']
        );

        return array_map(fn (Technician $t) => $this->toOutputDTO($t), $technicians);
    }

    public function getByIdAndCompany(int $id, Company $company): Technician
    {
        $technician = $this->repository->findOneBy(['id' => $id, 'company' => $company]);

        if (!$technician) {
            throw new NotFoundHttpException('TECHNICIAN_NOT_FOUND');
        }

        return $technician;
    }

    public function create(TechnicianInputDTO $dto, Company $company): Technician
    {
        $technician = $this->mapper->toEntity($dto, $company);
        $this->em->persist($technician);
        $this->em->flush();

        return $technician;
    }

    public function update(int $id, TechnicianInputDTO $dto, Company $company): Technician
    {
        $technician = $this->getByIdAndCompany($id, $company);

        $this->mapper->toEntity($dto, $company, $technician);

        $this->em->flush();

        return $technician;
    }

    public function delete(int $id, Company $company): void
    {
        $technician = $this->getByIdAndCompany($id, $company);

        $signature = $technician->getSignature();
        if ($signature && $signature->getFileName()) {
            $this->fileService->remove($this->getSignatureSubDir($company), $signature->getFileName());
        }

        // A remoção da linha de `signature` no banco acontece via ON DELETE CASCADE da FK.
        $this->em->remove($technician);
        $this->em->flush();
    }

    public function toOutputDTO(Technician $technician): TechnicianOutputDTO
    {
        $signature = $technician->getSignature();
        $signatureUrl = null;

        if ($signature && $signature->getFileName()) {
            $signatureUrl = $this->fileService->getPublicUrl(
                $this->getSignatureSubDir($technician->getCompany()),
                $signature->getFileName()
            );
        }

        return $this->mapper->toOutputDTO($technician, $signatureUrl);
    }

    private function getSignatureSubDir(Company $company): string
    {
        return $company->getSubDir('/signature');
    }
}
