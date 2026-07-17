<?php

namespace Weboldalnet\CommerceCore\Providers\Payment;

use Weboldalnet\CommerceCore\Contracts\PaymentProviderInterface;
use Weboldalnet\CommerceCore\Data\PaymentCallbackResult;
use Weboldalnet\CommerceCore\Data\PaymentCreateResult;
use Weboldalnet\CommerceCore\Data\PaymentRefundData;
use Weboldalnet\CommerceCore\Data\PaymentRefundResult;
use Weboldalnet\CommerceCore\Data\PaymentRequestData;
use Weboldalnet\CommerceCore\Status\PaymentStatus;

class CashOnDeliveryPaymentProvider implements PaymentProviderInterface
{
    public function getCode()
    {
        return 'cod';
    }

    public function getName()
    {
        return 'Utánvét';
    }

    public function isOnline()
    {
        return false;
    }

    public function createPayment(PaymentRequestData $data)
    {
        return PaymentCreateResult::success([
            'status' => PaymentStatus::PENDING,
            'provider' => $this->getCode(),
            'message' => 'Utánvétes fizetés létrehozva. A fizetés a kézbesítéskor esedékes.',
        ]);
    }

    public function handleReturn(array $payload)
    {
        return PaymentCallbackResult::fromArray([
            'success' => true,
            'status' => PaymentStatus::PENDING,
            'provider' => $this->getCode(),
            'raw_payload' => $payload,
        ]);
    }

    public function handleCallback(array $payload)
    {
        return PaymentCallbackResult::fromArray([
            'success' => true,
            'status' => PaymentStatus::PENDING,
            'provider' => $this->getCode(),
            'raw_payload' => $payload,
        ]);
    }

    public function refund(PaymentRefundData $data)
    {
        return PaymentRefundResult::failure([
            'provider' => $this->getCode(),
            'message' => 'Utánvétes fizetésnél visszatérítés nem automatizálható.',
        ]);
    }
}
