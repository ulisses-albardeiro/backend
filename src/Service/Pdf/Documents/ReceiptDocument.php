<?php

namespace App\Service\Pdf\Documents;

use App\DTO\Response\ReceiptOutputDTO;
use App\DTO\Response\CompanyOutputDTO;
use App\DTO\Response\CustomerOutputDTO;
use App\Service\Pdf\Interfaces\ExportableDocumentInterface;

class ReceiptDocument implements ExportableDocumentInterface
{
    public function __construct(
        private ReceiptOutputDTO $receipt,
        private CompanyOutputDTO $company,
        private CustomerOutputDTO $customer,
    ) {}

    public function getTemplate(): string
    {
        return 'pdf/receipt.html.twig';
    }

    public function getData(): array
    {
        return [
            'receipt'  => $this->receipt,
            'customer' => $this->customer,
            'company'  => $this->company,
        ];
    }

    public function getFileName(): string
    {
        return "recibo-{$this->receipt->code}.pdf";
    }
}
