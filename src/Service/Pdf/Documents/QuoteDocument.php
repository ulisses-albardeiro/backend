<?php

namespace App\Service\Pdf\Documents;

use App\DTO\Response\QuoteOutputDTO;
use App\DTO\Response\CompanyOutputDTO;
use App\DTO\Response\CustomerOutputDTO;
use App\Service\Pdf\Interfaces\ExportableDocumentInterface;

class QuoteDocument implements ExportableDocumentInterface
{
    public function __construct(
        private QuoteOutputDTO $quote,
        private CompanyOutputDTO $company,
        private CustomerOutputDTO $customer,
        ) {}

    public function getTemplate(): string
    {
        return 'pdf/quote.html.twig';
    }

    public function getData(): array
    {
        return [
            'quote' => $this->quote,
            'customer' => $this->customer,
            'company'  => $this->company,
        ];
    }

    public function getFileName(): string
    {
        return "orcamento-{$this->quote->code}.pdf";
    }
}
