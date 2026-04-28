<?php

namespace App\Service\Product;

use App\DTO\Request\Product\BrandInputDTO;
use App\DTO\Response\Product\BrandOutputDTO;
use App\Entity\Company;
use App\Entity\Product\Brand;
use App\Mapper\Product\BrandMapper;
use App\Repository\Product\BrandRepository;
use App\Repository\Product\ProductRepository;
use App\Service\FileService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class BrandService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private BrandMapper $mapper,
        private BrandRepository $repository,
        private ProductRepository $productRepository,
        private FileService $fileService
    ) {}

    public function create(BrandInputDTO $dto, Company $company, ?UploadedFile $logoFile): BrandOutputDTO
    {
        $brand = $this->mapper->toEntity($dto);
        $brand->setCompany($company);

        $this->entityManager->persist($brand);
        $this->entityManager->flush();

        if ($logoFile) {
            $subDir = $this->getSubDir($company);
            $fileName = $this->fileService->upload($logoFile, $subDir);
            $brand->setLogo($fileName);
            $this->entityManager->flush();
        }

        return $this->mapper->toOutput($brand, $this->getLogoUrl($brand));
    }

    public function update(int $id, BrandInputDTO $dto, Company $company, ?UploadedFile $logoFile): BrandOutputDTO
    {
        $brand = $this->repository->findOneBy(['id' => $id, 'company' => $company]);

        if (!$brand) {
            throw new \Exception("Marca não encontrada.");
        }

        $oldLogo = $brand->getLogo();
        $brand = $this->mapper->toEntity($dto, $brand);
        $this->entityManager->flush();

        if ($logoFile) {
            $subDir = $this->getSubDir($company);

            if ($oldLogo) {
                $this->fileService->remove($subDir, $oldLogo);
            }

            $fileName = $this->fileService->upload($logoFile, $subDir);
            $brand->setLogo($fileName);
            $this->entityManager->flush();
        }

        return $this->mapper->toOutput($brand, $this->getLogoUrl($brand));
    }

    public function delete(int $id, Company $company): void
    {
        $brand = $this->repository->findOneBy(['id' => $id, 'company' => $company]);

        if (!$brand) throw new \Exception("Marca não encontrada.");

        $hasProducts = $this->productRepository->findOneBy(['brand' => $brand]);
        if ($hasProducts) {
            throw new \Exception("Não é possível excluir marca com produtos vinculados.");
        }

        if ($brand->getLogo()) {
            $this->fileService->remove($this->getSubDir($company), $brand->getLogo());
        }

        $this->entityManager->remove($brand);
        $this->entityManager->flush();
    }

    public function listAll(Company $company): array
    {
        $brands = $this->repository->findBy(['company' => $company], ['name' => 'ASC']);

        return array_map(function ($brand) {
            return $this->mapper->toOutput($brand, $this->getLogoUrl($brand));
        }, $brands);
    }

    private function getSubDir(Company $company): string
    {
        if ($company->getCreatedAt()) {
            return 'company_' . md5($company->getCreatedAt()->format('U')) . '/brands';
        }

        return '';
    }

    private function getLogoUrl(Brand $brand): string
    {
        return $this->fileService->getPublicUrl($this->getSubDir($brand->getCompany()), $brand->getLogo());
    }
}
