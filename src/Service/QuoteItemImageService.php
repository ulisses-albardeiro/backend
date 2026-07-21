<?php

namespace App\Service;

use App\Entity\Company;
use App\Entity\Quote\QuoteItem;
use App\Entity\Quote\QuoteItemImage;
use App\Repository\QuoteItemImageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class QuoteItemImageService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private QuoteItemImageRepository $quoteItemImageRepository,
        private FileService $fileService
    ) {}

    /**
     * @param UploadedFile[] $files
     */
    public function addImages(QuoteItem $item, Company $company, array $files): void
    {
        $subDir = $this->getSubDir($company);

        foreach ($files as $file) {
            if (!$file instanceof UploadedFile) continue;

            $fileName = $this->fileService->upload($file, $subDir);

            $image = new QuoteItemImage();
            $image->setPath($fileName);
            $image->setIsMain($item->getImages()->isEmpty());
            $image->setSortOrder($item->getImages()->count() + 1);

            $item->addImage($image);
        }
    }

    public function removeImage(int $imageId, Company $company): void
    {
        $image = $this->quoteItemImageRepository->find($imageId);

        if (!$image || $image->getQuoteItem()->getQuote()->getCompany()->getId() !== $company->getId()) {
            throw new NotFoundHttpException('QUOTE_ITEM_IMAGE_NOT_FOUND');
        }

        $item = $image->getQuoteItem();
        $item->removeImage($image);

        $this->entityManager->remove($image);
        $this->entityManager->flush();
    }

    public function formatImages(QuoteItem $item, Company $company): array
    {
        $subDir = $this->getSubDir($company);

        return $item->getImages()->map(fn(QuoteItemImage $img) => [
            'id' => $img->getId(),
            'url' => $this->fileService->getPublicUrl($subDir, $img->getPath()),
            'isMain' => $img->isMain(),
            'sortOrder' => $img->getSortOrder()
        ])->toArray();
    }

    public function getSubDir(Company $company): string
    {
        return 'company_' . md5($company->getCreatedAt()->format('U')) . '/quote_items';
    }
}
