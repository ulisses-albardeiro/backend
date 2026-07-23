<?php

namespace App\Service;

use App\Entity\Company;
use App\Entity\Signature;
use App\Entity\Technician;
use App\Repository\TechnicianRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class SignatureService
{
    public function __construct(
        private TechnicianRepository $technicianRepository,
        private EntityManagerInterface $em,
        private FileService $fileService,
    ) {}

    public function upsert(int $technicianId, Company $company, UploadedFile $file): Technician
    {
        $technician = $this->getTechnicianOrFail($technicianId, $company);
        $subDir = $company->getSubDir('/signature');

        $signature = $technician->getSignature();
        if ($signature && $signature->getFileName()) {
            $this->fileService->remove($subDir, $signature->getFileName());
        }

        if (!$signature) {
            $signature = new Signature();
            $signature->setTechnician($technician);
            $signature->setCompany($company);
        }

        $fileName = $this->fileService->upload($file, $subDir);
        $signature->setFileName($fileName);

        $this->em->persist($signature);
        $this->em->flush();

        return $technician;
    }

    public function delete(int $technicianId, Company $company): void
    {
        $technician = $this->getTechnicianOrFail($technicianId, $company);
        $signature = $technician->getSignature();

        if (!$signature) {
            throw new NotFoundHttpException('SIGNATURE_NOT_FOUND');
        }

        if ($signature->getFileName()) {
            $this->fileService->remove($company->getSubDir('/signature'), $signature->getFileName());
        }

        $this->em->remove($signature);
        $this->em->flush();
    }

    private function getTechnicianOrFail(int $technicianId, Company $company): Technician
    {
        $technician = $this->technicianRepository->findOneBy(['id' => $technicianId, 'company' => $company]);

        if (!$technician) {
            throw new NotFoundHttpException('TECHNICIAN_NOT_FOUND');
        }

        return $technician;
    }
}
