<?php

namespace Weboldalnet\CommerceCore\Providers\Shipping;

use Weboldalnet\CommerceCore\Contracts\ShippingProviderInterface;
use Weboldalnet\CommerceCore\Data\ShipmentCreateResult;
use Weboldalnet\CommerceCore\Data\ShipmentRequestData;
use Weboldalnet\CommerceCore\Data\ShippingRateRequestData;
use Weboldalnet\CommerceCore\Data\ShippingRateResult;
use Weboldalnet\CommerceCore\Status\ShippingStatus;

class PickupShippingProvider implements ShippingProviderInterface
{
    public function getCode()
    {
        return 'pickup';
    }

    public function getName()
    {
        return 'Személyes átvétel';
    }

    public function calculate(ShippingRateRequestData $data)
    {
        return ShippingRateResult::success([
            'provider' => $this->getCode(),
            'shipping_method' => 'pickup',
            'rate' => 0,
            'currency' => $data->currency,
            'is_free' => true,
            'message' => 'Személyes átvétel - ingyenes.',
        ]);
    }

    public function createShipment(ShipmentRequestData $data)
    {
        return ShipmentCreateResult::success([
            'status' => ShippingStatus::PREPARED,
            'provider' => $this->getCode(),
            'message' => 'Személyes átvételre előkészítve.',
        ]);
    }

    public function getTrackingUrl($trackingNumber)
    {
        return null;
    }
}
