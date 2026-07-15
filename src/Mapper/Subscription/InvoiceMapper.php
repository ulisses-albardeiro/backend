<?php

namespace App\Mapper\Subscription;

use App\Entity\Subscription\Invoice;
use App\DTO\Response\Subscription\InvoiceOutputDTO;

class InvoiceMapper
{
    public function toOutputDTO(Invoice $invoice): InvoiceOutputDTO
    {
        return new InvoiceOutputDTO(
            id: $invoice->getId(),
            status: $invoice->getStatus()->value,
            statusLabel: $invoice->getStatus()->getLabel(),
            billingType: $invoice->getBillingType()->value,
            billingTypeLabel: $invoice->getBillingType()->getLabel(),
            valueCents: $invoice->getValueCents(),
            dueDate: $invoice->getDueDate(),
            paidAt: $invoice->getPaidAt(),
            invoiceUrl: $invoice->getInvoiceUrl(),
        );
    }
}
