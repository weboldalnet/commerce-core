<?php

namespace Weboldalnet\CommerceCore\Providers\Shipping;

use Weboldalnet\CommerceCore\Contracts\ShippingProviderInterface;
use Weboldalnet\CommerceCore\Data\ShipmentCreateResult;
use Weboldalnet\CommerceCore\Data\ShipmentRequestData;
use Weboldalnet\CommerceCore\Data\ShippingRateRequestData;
use Weboldalnet\CommerceCore\Data\ShippingRateResult;
use Weboldalnet\CommerceCore\Status\ShippingStatus;

class ManualShippingProvider implements ShippingProviderInterface
{
    public function getCode()
    {
        return 'manual';
    }

    public function getName()
    {
        return 'Manuális szállítás';
    }

    public function calculate(ShippingRateRequestData $data)
    {
        return ShippingRateResult::success([
            'provider' => $this->getCode(),
            'shipping_method' => 'manual',
            'rate' => 0,
            'currency' => $data->currency,
            'message' => 'Manuális szállítás - díj egyedileg kerül meghatározásra.',
        ]);
    }

    public function createShipment(ShipmentRequestData $data)
    {
        return ShipmentCreateResult::success([
            'status' => ShippingStatus::PENDING,
            'provider' => $this->getCode(),
            'message' => 'Manuális szállítmány létrehozva.',
        ]);
    }

    public function getTrackingUrl($trackingNumber)
    {
        return null;
    }
}
