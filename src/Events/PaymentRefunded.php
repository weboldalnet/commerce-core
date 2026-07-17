<?php

namespace Weboldalnet\CommerceCore\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Weboldalnet\CommerceCore\Data\PaymentRefundResult;
use Weboldalnet\CommerceCore\Models\PaymentTransaction;

class PaymentRefunded
{
    use Dispatchable;

    public $orderId;
    public $provider;
    public $transaction;
    public $result;

    public function __construct($orderId, $provider, PaymentTransaction $transaction = null, PaymentRefundResult $result = null)
    {
        $this->orderId = $orderId;
        $this->provider = $provider;
        $this->transaction = $transaction;
        $this->result = $result;
    }
}
