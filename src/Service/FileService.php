<?php

namespace App\Service;

use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileService
{
    public function __construct(
        private string $targetDirectory,
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
}
