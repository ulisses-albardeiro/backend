<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileService
{
    public function __construct(
        private string $targetDirectory,
        private RequestStack $requestStack
    ) {}

    public function upload(UploadedFile $file, string $subDirectory): string
    {
        if (!$file->isValid()) {
            // Upload rejeitado pelo próprio PHP (ex.: maior que upload_max_filesize)
            // antes mesmo de chegar no Symfony — tmp_name fica vazio, e tentar
            // processá-lo (ex.: guessExtension) quebra com um erro de mime type
            // sem sentido pro usuário. Falha aqui com uma mensagem clara.
            throw new \InvalidArgumentException(\sprintf(
                'Falha ao enviar o arquivo "%s": %s',
                $file->getClientOriginalName(),
                $file->getErrorMessage()
            ));
        }

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

    public function getPath(string $subDirectory, ?string $fileName): string
    {
        if (!$fileName) {
            return '';
        }

        $filePath = $this->targetDirectory . '/' . $subDirectory . '/' . $fileName;
        if (!file_exists($filePath)) {
            return '';
        }

        return $filePath;
    }

    public function getBase64(string $subDirectory, ?string $fileName): string
    {
        if (!$fileName) {
            return '';
        }

        $filePath = $this->targetDirectory . '/' . $subDirectory . '/' . $fileName;

        if (!file_exists($filePath)) {
            return '';
        }

        try {
            $data = file_get_contents($filePath);
            $type = pathinfo($filePath, PATHINFO_EXTENSION);

            return 'data:image/' . $type . ';base64,' . base64_encode($data);
        } catch (\Exception) {
            return '';
        }
    }
}
