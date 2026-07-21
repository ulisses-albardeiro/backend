<?php

namespace App\Service\Order;

use App\Entity\Company;
use App\Entity\Order\WorkOrderItem;
use App\Entity\Order\WorkOrderItemImage;
use App\Entity\Quote\QuoteItem;
use App\Repository\Order\WorkOrderItemImageRepository;
use App\Service\FileService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class WorkOrderItemImageService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private WorkOrderItemImageRepository $workOrderItemImageRepository,
        private FileService $fileService
    ) {}

    /**
     * @param UploadedFile[] $files
     */
    public function addImages(WorkOrderItem $item, Company $company, array $files): void
    {
        $subDir = $this->getSubDir($company);

        foreach ($files as $file) {
            if (!$file instanceof UploadedFile) continue;

            $fileName = $this->fileService->upload($file, $subDir);

            $image = new WorkOrderItemImage();
            $image->setPath($fileName);
            $image->setIsMain($item->getImages()->isEmpty());
            $image->setSortOrder($item->getImages()->count() + 1);

            $item->addImage($image);
        }
    }

    /**
     * Copia as fotos de um item de orçamento para um item de OS sem novo upload —
     * QuoteItemImage e WorkOrderItemImage compartilham o mesmo subdiretório físico
     * (docs_images), então o mesmo nome de arquivo já resolve pro arquivo certo.
     */
    public function copyFromQuoteItem(WorkOrderItem $item, QuoteItem $sourceItem): void
    {
        foreach ($sourceItem->getImages() as $sourceImage) {
            $image = new WorkOrderItemImage();
            $image->setPath($sourceImage->getPath());
            $image->setIsMain($sourceImage->isMain());
            $image->setSortOrder($sourceImage->getSortOrder());

            $item->addImage($image);
        }
    }

    public function removeImage(int $imageId, Company $company): void
    {
        $image = $this->workOrderItemImageRepository->find($imageId);

        if (!$image || $image->getWorkOrderItem()->getWorkOrder()->getCompany()->getId() !== $company->getId()) {
            throw new NotFoundHttpException('WORK_ORDER_ITEM_IMAGE_NOT_FOUND');
        }

        $item = $image->getWorkOrderItem();
        $item->removeImage($image);

        $this->entityManager->remove($image);
        $this->entityManager->flush();
    }

    public function formatImages(WorkOrderItem $item, Company $company): array
    {
        $subDir = $this->getSubDir($company);

        return $item->getImages()->map(fn(WorkOrderItemImage $img) => [
            'id' => $img->getId(),
            'url' => $this->fileService->getPublicUrl($subDir, $img->getPath()),
            'isMain' => $img->isMain(),
            'sortOrder' => $img->getSortOrder()
        ])->toArray();
    }

    public function getSubDir(Company $company): string
    {
        return 'company_' . md5($company->getCreatedAt()->format('U')) . '/docs_images';
    }
}
