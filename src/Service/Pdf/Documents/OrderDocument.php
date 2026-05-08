<?php

namespace App\Service\Pdf\Documents;

use App\DTO\Response\Quote\QuoteOutputDTO;
use App\DTO\Response\CompanyOutputDTO;
use App\DTO\Response\Customer\CustomerOutputDTO;
use App\DTO\Response\Order\WorkOrderOutputDTO;
use App\Service\Pdf\Interfaces\ExportableDocumentInterface;

class OrderDocument implements ExportableDocumentInterface
{
    public function __construct(
        private WorkOrderOutputDTO $order,
        private CompanyOutputDTO $company,
        private CustomerOutputDTO $customer,
        ) {}

    public function getTemplate(): string
    {
        return 'pdf/order.html.twig';
    }

    public function getData(): array
    {
        return [
            'order' => $this->order,
            'customer' => $this->customer,
            'company'  => $this->company,
        ];
    }

    public function getFileName(): string
    {
        return "orcamento-{$this->order->code}.pdf";
    }
}
