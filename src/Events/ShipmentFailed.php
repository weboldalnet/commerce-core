<?php

namespace Weboldalnet\CommerceCore\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Weboldalnet\CommerceCore\Data\ShipmentCreateResult;
use Weboldalnet\CommerceCore\Models\Shipment;

class ShipmentFailed
{
    use Dispatchable;

    public $orderId;
    public $provider;
    public $shipment;
    public $result;

    public function __construct($orderId, $provider, Shipment $shipment = null, ShipmentCreateResult $result = null)
    {
        $this->orderId = $orderId;
        $this->provider = $provider;
        $this->shipment = $shipment;
        $this->result = $result;
    }
}
