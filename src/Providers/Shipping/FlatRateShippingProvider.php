<?php

namespace Weboldalnet\CommerceCore\Providers\Shipping;

use Weboldalnet\CommerceCore\Contracts\ShippingProviderInterface;
use Weboldalnet\CommerceCore\Data\ShipmentCreateResult;
use Weboldalnet\CommerceCore\Data\ShipmentRequestData;
use Weboldalnet\CommerceCore\Data\ShippingRateRequestData;
use Weboldalnet\CommerceCore\Data\ShippingRateResult;
use Weboldalnet\CommerceCore\Status\ShippingStatus;

class FlatRateShippingProvider implements ShippingProviderInterface
{
    protected $config;

    public function __construct(array $config = [])
    {
        $this->config = $config;
    }

    public function getCode()
    {
        return 'flat_rate';
    }

    public function getName()
    {
        return 'Fix szállítási díj';
    }

    public function getRate()
    {
        return $this->config['rate'] ?? config('commerce-core.shipping.providers.flat_rate.rate', 1490);
    }

    public function getCurrency()
    {
        return $this->config['currency'] ?? config('commerce-core.shipping.providers.flat_rate.currency', 'HUF');
    }

    public function getFreeAbove()
    {
        return $this->config['free_above'] ?? config('commerce-core.shipping.providers.flat_rate.free_above', null);
    }

    public function calculate(ShippingRateRequestData $data)
    {
        $freeAbove = $this->getFreeAbove();
        $isFree = $freeAbove !== null && $data->cartTotal >= $freeAbove;
        $rate = $isFree ? 0 : $this->getRate();

        return ShippingRateResult::success([
            'provider' => $this->getCode(),
            'shipping_method' => 'flat_rate',
            'rate' => $rate,
            'currency' => $this->getCurrency(),
            'is_free' => $isFree,
            'message' => $isFree ? 'Ingyenes szállítás.' : 'Fix szállítási díj: ' . $rate . ' ' . $this->getCurrency(),
        ]);
    }

    public function createShipment(ShipmentRequestData $data)
    {
        return ShipmentCreateResult::success([
            'status' => ShippingStatus::PENDING,
            'provider' => $this->getCode(),
            'message' => 'Fix díjas szállítmány létrehozva.',
        ]);
    }

    public function getTrackingUrl($trackingNumber)
    {
        return null;
    }
}
