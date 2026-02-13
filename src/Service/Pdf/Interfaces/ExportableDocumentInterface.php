<?php

namespace App\Service\Pdf\Interfaces;

interface ExportableDocumentInterface
{
    public function getTemplate(): string;
    public function getData(): array;
    public function getFileName(): string;
}
