<?php

namespace Weboldalnet\CommerceCore\Contracts;

use Weboldalnet\CommerceCore\Data\PaymentRequestData;
use Weboldalnet\CommerceCore\Data\PaymentCreateResult;
use Weboldalnet\CommerceCore\Data\PaymentCallbackResult;
use Weboldalnet\CommerceCore\Data\PaymentRefundData;
use Weboldalnet\CommerceCore\Data\PaymentRefundResult;

interface PaymentProviderInterface
{
    public function getCode();

    public function getName();

    public function isOnline();

    public function createPayment(PaymentRequestData $data);

    public function handleReturn(array $payload);

    public function handleCallback(array $payload);

    public function refund(PaymentRefundData $data);
}
