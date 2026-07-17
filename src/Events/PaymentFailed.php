<?php

namespace Weboldalnet\CommerceCore\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Weboldalnet\CommerceCore\Data\PaymentCallbackResult;
use Weboldalnet\CommerceCore\Models\PaymentTransaction;

class PaymentFailed
{
    use Dispatchable;

    public $orderId;
    public $provider;
    public $transaction;
    public $result;

    public function __construct($orderId, $provider, PaymentTransaction $transaction = null, PaymentCallbackResult $result = null)
    {
        $this->orderId = $orderId;
        $this->provider = $provider;
        $this->transaction = $transaction;
        $this->result = $result;
    }
}
