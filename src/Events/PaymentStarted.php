<?php

namespace Weboldalnet\CommerceCore\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Weboldalnet\CommerceCore\Data\PaymentCreateResult;

class PaymentStarted
{
    use Dispatchable;

    public $orderId;
    public $provider;
    public $transactionId;
    public $result;

    public function __construct($orderId, $provider, $transactionId, PaymentCreateResult $result = null)
    {
        $this->orderId = $orderId;
        $this->provider = $provider;
        $this->transactionId = $transactionId;
        $this->result = $result;
    }
}
