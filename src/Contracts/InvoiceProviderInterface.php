<?php

namespace Weboldalnet\CommerceCore\Contracts;

use Weboldalnet\CommerceCore\Data\InvoiceRequestData;
use Weboldalnet\CommerceCore\Data\InvoiceCreateResult;
use Weboldalnet\CommerceCore\Data\InvoiceVoidResult;

interface InvoiceProviderInterface
{
    public function getCode();

    public function getName();

    public function createInvoice(InvoiceRequestData $data);

    public function voidInvoice($invoiceNumber);

    public function downloadPdf($invoiceNumber);
}
