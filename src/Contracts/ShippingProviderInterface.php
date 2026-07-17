<?php

namespace Weboldalnet\CommerceCore\Contracts;

use Weboldalnet\CommerceCore\Data\ShippingRateRequestData;
use Weboldalnet\CommerceCore\Data\ShippingRateResult;
use Weboldalnet\CommerceCore\Data\ShipmentRequestData;
use Weboldalnet\CommerceCore\Data\ShipmentCreateResult;

interface ShippingProviderInterface
{
    public function getCode();

    public function getName();

    public function calculate(ShippingRateRequestData $data);

    public function createShipment(ShipmentRequestData $data);

    public function getTrackingUrl($trackingNumber);
}
