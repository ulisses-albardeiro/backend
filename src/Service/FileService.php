<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RequestStack;

class FileService
{
    public function __construct(
        private string $targetDirectory,
        private RequestStack $requestStack
    ) {}

    public function upload(UploadedFile $file, string $subDirectory): string
    {
        $uploadPath = $this->targetDirectory . '/' . $subDirectory;

        if (!is_dir($uploadPath)) {
            mkdir($uploadPath, 0775, true);
        }

        $fileName = uniqid() . '.' . $file->guessExtension();

        $file->move($uploadPath, $fileName);

        return $fileName;
    }

    public function remove(string $subDirectory, string $fileName): void
    {
        $filePath = $this->targetDirectory . '/' . $subDirectory . '/' . $fileName;

        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }

    public function getPublicUrl(string $subDirectory, ?string $fileName): string
    {
        if (!$fileName) {
            return '';
        }

        $filePath = $this->targetDirectory . '/' . $subDirectory . '/' . $fileName;
        if (!file_exists($filePath)) {
            return '';
        }

        $request = $this->requestStack->getCurrentRequest();
        $baseUrl = $request ? $request->getSchemeAndHttpHost() : '';

        return "$baseUrl/uploads/$subDirectory/$fileName";
    }
}
