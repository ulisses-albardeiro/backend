<?php

namespace App\Mapper\Product;

use App\DTO\Request\Product\ProductInputDTO;
use App\DTO\Response\Product\ProductOutputDTO;
use App\Entity\Product\Product;
use App\Repository\Product\BrandRepository;
use App\Repository\Product\ProductCategoryRepository;
use App\Repository\Product\SupplierRepository;

class ProductMapper
{
    public function __construct(
        private ProductCategoryRepository $categoryRepository,
        private BrandRepository $brandRepository,
        private SupplierRepository $supplierRepository,
        private ProductCategoryMapper $categoryMapper,
        private BrandMapper $brandMapper,
        private SupplierMapper $supplierMapper
    ) {}

    public function toEntity(ProductInputDTO $dto, ?Product $entity = null): Product
    {
        $entity = $entity ?? new Product();

        $entity->setName($dto->name);
        $entity->setSku($dto->sku);
        $entity->setBarcode($dto->barcode);
        $entity->setDescription($dto->description);
        $entity->setPurchasePrice($dto->purchasePrice);
        $entity->setSalePrice($dto->salePrice);
        $entity->setUnit($dto->unit);

        $entity->setStockQuantity($dto->stockQuantity);

        $entity->setMinStock($dto->minStock);
        $entity->setNcm($dto->ncm);
        $entity->setStatus($dto->status);

        $entity->setCategory($this->categoryRepository->find($dto->categoryId));

        if ($dto->brandId) {
            $entity->setBrand($this->brandRepository->find($dto->brandId));
        }

        if ($dto->supplierId) {
            $entity->setSupplier($this->supplierRepository->find($dto->supplierId));
        }

        return $entity;
    }

    public function toOutput(Product $entity, ?array $productImages): ProductOutputDTO
    {
        return new ProductOutputDTO(
            id: $entity->getId(),
            name: $entity->getName(),
            category: $this->categoryMapper->toOutput($entity->getCategory()),
            brand: $entity->getBrand() ? $this->brandMapper->toOutput($entity->getBrand(), $entity->getBrand()->getLogo()) : null,
            supplier: $entity->getSupplier() ? $this->supplierMapper->toOutput($entity->getSupplier()) : null,
            sku: $entity->getSku(),
            barcode: $entity->getBarcode(),
            description: $entity->getDescription(),
            purchasePrice: $entity->getPurchasePrice(),
            salePrice: $entity->getSalePrice(),
            unitLabel: $entity->getUnit()->getLabel(),
            unitCode: $entity->getUnit()->value,
            stockQuantity: $entity->getStockQuantity() ?? 0,
            minStock: $entity->getMinStock() ?? 0,
            ncm: $entity->getNcm(),
            statusLabel: $entity->getStatus()->getLabel(),
            images: $productImages ?? [],
            createdAt: $entity->getCreatedAt(),
        );
    }
}
