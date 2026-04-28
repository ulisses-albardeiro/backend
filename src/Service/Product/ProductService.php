<?php

namespace App\Service\Product;

use App\DTO\Request\Product\ProductInputDTO;
use App\DTO\Response\Product\ProductOutputDTO;
use App\Entity\Company;
use App\Entity\Product\Product;
use App\Entity\Product\ProductImage;
use App\Enum\Product\InventoryMovementType;
use App\Mapper\Product\ProductMapper;
use App\Repository\Product\ProductRepository;
use App\Service\FileService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ProductService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ProductRepository $productRepository,
        private ProductMapper $productMapper,
        private InventoryService $inventoryService,
        private FileService $fileService
    ) {}

    /**
     * @param UploadedFile[] $imageFiles
     */
    public function create(ProductInputDTO $dto, Company $company, array $imageFiles = []): ProductOutputDTO
    {
        $product = $this->productMapper->toEntity($dto);
        $product->setCompany($company);

        $this->entityManager->persist($product);
        
        // Se houver imagens, processamos antes do flush final
        if (!empty($imageFiles)) {
            $this->handleImagesUpload($product, $company, $imageFiles);
        }

        $this->entityManager->flush();

        if ($dto->stockQuantity > 0) {
            $this->inventoryService->registerMovement(
                $product,
                $dto->stockQuantity,
                InventoryMovementType::INPUT,
                'Estoque inicial no cadastro do produto'
            );
        }

        return $this->productMapper->toOutput($product, $this->formatProductImagesUrls($product));
    }

    /**
     * @param UploadedFile[] $imageFiles
     */
    public function update(int $id, ProductInputDTO $dto, Company $company, array $imageFiles = []): ProductOutputDTO
    {
        $product = $this->productRepository->findOneBy(['id' => $id, 'company' => $company]);

        if (!$product) {
            throw new NotFoundHttpException("Produto não encontrado.");
        }

        $product = $this->productMapper->toEntity($dto, $product);

        if (!empty($imageFiles)) {
            $this->handleImagesUpload($product, $company, $imageFiles);
        }

        $this->entityManager->flush();

        return $this->productMapper->toOutput($product, $this->formatProductImagesUrls($product));
    }

    public function listAll(Company $company): array
    {
        $products = $this->productRepository->findBy(['company' => $company]);
        return array_map(
            fn($p) => $this->productMapper->toOutput($p, $this->formatProductImagesUrls($p)), 
            $products
        );
    }

    public function getById(int $id, Company $company): ProductOutputDTO
    {
        $product = $this->productRepository->findOneBy(['id' => $id, 'company' => $company]);

        if (!$product) {
            throw new NotFoundHttpException('PRODUCT_NOT_FOUND');
        }

        return $this->productMapper->toOutput($product, $this->formatProductImagesUrls($product));
    }

    public function delete(int $id, Company $company): void
    {
        $product = $this->productRepository->findOneBy(['id' => $id, 'company' => $company]);

        if (!$product) {
            throw new NotFoundHttpException('PRODUCT_NOT_FOUND');
        }

        // Remover arquivos físicos antes de deletar a entidade
        $subDir = $this->getSubDir($company);
        foreach ($product->getProductImages() as $image) {
            $this->fileService->remove($subDir, $image->getPath());
        }

        try {
            $this->entityManager->remove($product);
            $this->entityManager->flush();
        } catch (\Exception $e) {
            throw new \Exception('CANNOT_DELETE_PRODUCT_IN_USE');
        }
    }

    // --- Métodos Auxiliares ---

    private function handleImagesUpload(Product $product, Company $company, array $imageFiles): void
    {
        $subDir = $this->getSubDir($company);
        
        foreach ($imageFiles as $index => $file) {
            if (!$file instanceof UploadedFile) continue;

            $fileName = $this->fileService->upload($file, $subDir);

            $productImage = new ProductImage();
            $productImage->setProduct($product);
            $productImage->setPath($fileName);
            // Define a primeira imagem como principal se o produto não tiver nenhuma
            $productImage->setIsMain($product->getProductImages()->isEmpty() && $index === 0);
            $productImage->setSortOrder($product->getProductImages()->count() + 1);

            $this->entityManager->persist($productImage);
            $product->addProductImage($productImage);
        }
    }

    private function formatProductImagesUrls(Product $product): array
    {
        $subDir = $this->getSubDir($product->getCompany());
        
        return $product->getProductImages()->map(fn(ProductImage $img) => [
            'id' => $img->getId(),
            'url' => $this->fileService->getPublicUrl($subDir, $img->getPath()),
            'isMain' => $img->isMain(),
            'sortOrder' => $img->getSortOrder()
        ])->toArray();
    }

    private function getSubDir(Company $company): string
    {
        return 'company_' . md5($company->getCreatedAt()->format('U')) . '/products';
    }
}
