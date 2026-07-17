<?php

namespace Weboldalnet\CommerceCore\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Weboldalnet\CommerceCore\Data\InvoiceCreateResult;
use Weboldalnet\CommerceCore\Models\InvoiceDocument;

class InvoiceFailed
{
    use Dispatchable;

    public $orderId;
    public $provider;
    public $document;
    public $result;

    public function __construct($orderId, $provider, InvoiceDocument $document = null, InvoiceCreateResult $result = null)
    {
        $this->orderId = $orderId;
        $this->provider = $provider;
        $this->document = $document;
        $this->result = $result;
    }
}
