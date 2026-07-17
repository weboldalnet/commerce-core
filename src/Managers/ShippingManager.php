<?php

namespace Weboldalnet\CommerceCore\Managers;

use Weboldalnet\CommerceCore\Contracts\ShippingProviderInterface;
use Weboldalnet\CommerceCore\Data\ShipmentRequestData;
use Weboldalnet\CommerceCore\Data\ShippingRateRequestData;
use Weboldalnet\CommerceCore\Exceptions\ProviderNotEnabledException;
use Weboldalnet\CommerceCore\Exceptions\ProviderNotFoundException;

class ShippingManager
{
    protected $providers = [];

    public function __construct()
    {
        $this->loadProviders();
    }

    protected function loadProviders()
    {
        $providerConfigs = config('commerce-core.shipping.providers', []);
        $enabled = config('commerce-core.shipping.enabled', []);

        foreach ($providerConfigs as $code => $providerConfig) {
            if (empty($providerConfig['enabled'])) {
                continue;
            }
            if (!in_array($code, $enabled)) {
                continue;
            }
            $driverClass = $providerConfig['driver'] ?? null;
            if (!$driverClass || !class_exists($driverClass)) {
                continue;
            }
            $instance = new $driverClass($providerConfig);
            if (!($instance instanceof ShippingProviderInterface)) {
                continue;
            }
            $this->providers[$code] = $instance;
        }
    }

    public function register($code, ShippingProviderInterface $provider)
    {
        $this->providers[$code] = $provider;
    }

    public function getProvider($code)
    {
        if (!isset($this->providers[$code])) {
            $providerConfig = config('commerce-core.shipping.providers.' . $code);
            if (!$providerConfig) {
                throw new ProviderNotFoundException("Shipping provider '{$code}' nem található.");
            }
            throw new ProviderNotEnabledException("Shipping provider '{$code}' nincs engedélyezve.");
        }

        return $this->providers[$code];
    }

    public function getDefaultProvider()
    {
        $default = config('commerce-core.shipping.default');
        return $this->getProvider($default);
    }

    public function getEnabledProviders()
    {
        return $this->providers;
    }

    public function hasProvider($code)
    {
        return isset($this->providers[$code]);
    }

    public function calculate($code, ShippingRateRequestData $data)
    {
        return $this->getProvider($code)->calculate($data);
    }

    public function createShipment($code, ShipmentRequestData $data)
    {
        return $this->getProvider($code)->createShipment($data);
    }

    public function getTrackingUrl($code, $trackingNumber)
    {
        return $this->getProvider($code)->getTrackingUrl($trackingNumber);
    }
}
