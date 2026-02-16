<?php

namespace App\Service;

use App\Entity\Company;
use App\Entity\PriceList;
use App\Mapper\CompanyMapper;
use App\Mapper\PriceListMapper;
use App\DTO\Request\PriceListInputDTO;
use App\DTO\Response\PriceListOutputDTO;
use App\Repository\PriceListRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Service\Pdf\Documents\PriceListDocument;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PriceListService
{
    public function __construct(
        private PriceListMapper $mapper,
        private FileService $fileService,
        private EntityManagerInterface $em,
        private CompanyMapper $companyMapper,
        private PriceListRepository $repository,
    ) {}

    public function listAllByCompany(Company $company): array
    {
        $lists = $this->repository->findBy(
            ['company' => $company],
            ['createdAt' => 'DESC']
        );

        return array_map(fn($list) => $this->mapper->toOutputDTO($list), $lists);
    }

    public function getByIdAndCompany(int $id, Company $company): PriceListOutputDTO
    {
        $priceList = $this->repository->findOneBy([
            'id' => $id,
            'company' => $company
        ]);

        if (!$priceList) {
            throw new NotFoundHttpException('PRICE_LIST_NOT_FOUND');
        }

        return $this->mapper->toOutputDTO($priceList);
    }

    public function create(PriceListInputDTO $dto, Company $company): PriceListOutputDTO
    {
        $priceList = $this->mapper->toEntity($dto, $company);

        $this->em->persist($priceList);
        $this->em->flush();

        return $this->mapper->toOutputDTO($priceList);
    }

    public function update(int $id, PriceListInputDTO $dto, Company $company): PriceListOutputDTO
    {
        $priceList = $this->repository->findOneBy([
            'id' => $id,
            'company' => $company
        ]);

        if (!$priceList) {
            throw new NotFoundHttpException('PRICE_LIST_NOT_FOUND');
        }

        $this->mapper->toEntity($dto, $company, $priceList);

        $this->em->flush();

        return $this->mapper->toOutputDTO($priceList);
    }

    public function delete(int $id, Company $company): void
    {
        $priceList = $this->repository->findOneBy([
            'id' => $id,
            'company' => $company
        ]);

        if (!$priceList) {
            return;
        }

        $this->em->remove($priceList);
        $this->em->flush();
    }

    public function getPriceListDocument(int $id, Company $company): PriceListDocument
    {
        $priceListEntity = $this->repository->findOneBy([
            'id' => $id,
            'company' => $company
        ]);

        if (!$priceListEntity) {
            throw new NotFoundHttpException('PRICE_LIST_NOT_FOUND');
        }

        $priceListDto = $this->mapper->toOutputDTO($priceListEntity);
        
        $logoBase64 = $this->fileService->getBase64(
            $company->getSubDir('/logo'), 
            $company->getLogo()
        );

        $companyDto = $this->companyMapper->toOutputDTO($company, $logoBase64);

        return new PriceListDocument($companyDto, $priceListDto);
    }
}
