<?php

namespace Weboldalnet\CommerceCore\Providers\Invoice;

use Weboldalnet\CommerceCore\Contracts\InvoiceProviderInterface;
use Weboldalnet\CommerceCore\Data\InvoiceCreateResult;
use Weboldalnet\CommerceCore\Data\InvoiceRequestData;
use Weboldalnet\CommerceCore\Data\InvoiceVoidResult;
use Weboldalnet\CommerceCore\Status\InvoiceStatus;

class ManualInvoiceProvider implements InvoiceProviderInterface
{
    public function getCode()
    {
        return 'manual';
    }

    public function getName()
    {
        return 'Manuális számlázás';
    }

    public function createInvoice(InvoiceRequestData $data)
    {
        return InvoiceCreateResult::success([
            'status' => InvoiceStatus::INVOICED,
            'provider' => $this->getCode(),
            'message' => 'Manuális számla bejegyzés létrehozva. A számlát manuálisan kell kiállítani.',
        ]);
    }

    public function voidInvoice($invoiceNumber)
    {
        return InvoiceVoidResult::success([
            'status' => InvoiceStatus::VOIDED,
            'provider' => $this->getCode(),
            'invoice_number' => $invoiceNumber,
            'message' => 'Manuális számla érvénytelenítve.',
        ]);
    }

    public function downloadPdf($invoiceNumber)
    {
        return null;
    }
}
