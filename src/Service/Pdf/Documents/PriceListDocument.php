<?php

namespace App\Service\Pdf\Documents;

use App\DTO\Response\CompanyOutputDTO;
use App\DTO\Response\PriceListOutputDTO;
use App\Service\Pdf\Interfaces\ExportableDocumentInterface;

class PriceListDocument implements ExportableDocumentInterface
{
    public function __construct(
        private CompanyOutputDTO $company,
        private PriceListOutputDTO $priceList,
    ) {}

    public function getTemplate(): string
    {
        return 'pdf/price_list.html.twig';
    }

    public function getData(): array
    {
        return [
            'priceList' => $this->priceList,
            'company'   => $this->company,
        ];
    }

    public function getFileName(): string
    {
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $this->priceList->title)));
        return "lista-materiais-{$slug}.pdf";
    }
}