<?php

namespace Weboldalnet\CommerceCore\Managers;

use Weboldalnet\CommerceCore\Contracts\PaymentProviderInterface;
use Weboldalnet\CommerceCore\Data\PaymentRefundData;
use Weboldalnet\CommerceCore\Data\PaymentRequestData;
use Weboldalnet\CommerceCore\Exceptions\InvalidProviderException;
use Weboldalnet\CommerceCore\Exceptions\ProviderNotEnabledException;
use Weboldalnet\CommerceCore\Exceptions\ProviderNotFoundException;

class PaymentManager
{
    protected $providers = [];

    public function __construct()
    {
        $this->loadProviders();
    }

    protected function loadProviders()
    {
        $providerConfigs = config('commerce-core.payments.providers', []);
        $enabled = config('commerce-core.payments.enabled', []);

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
            $instance = new $driverClass();
            if (!($instance instanceof PaymentProviderInterface)) {
                continue;
            }
            $this->providers[$code] = $instance;
        }
    }

    public function register($code, PaymentProviderInterface $provider)
    {
        $this->providers[$code] = $provider;
    }

    public function getProvider($code)
    {
        $enabledCodes = config('commerce-core.payments.enabled', []);

        if (!isset($this->providers[$code])) {
            $providerConfig = config('commerce-core.payments.providers.' . $code);
            if (!$providerConfig) {
                throw new ProviderNotFoundException("Payment provider '{$code}' nem található.");
            }
            throw new ProviderNotEnabledException("Payment provider '{$code}' nincs engedélyezve.");
        }

        return $this->providers[$code];
    }

    public function getDefaultProvider()
    {
        $default = config('commerce-core.payments.default');
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

    public function createPayment($code, PaymentRequestData $data)
    {
        return $this->getProvider($code)->createPayment($data);
    }

    public function handleReturn($code, array $payload)
    {
        return $this->getProvider($code)->handleReturn($payload);
    }

    public function handleCallback($code, array $payload)
    {
        return $this->getProvider($code)->handleCallback($payload);
    }

    public function refund($code, PaymentRefundData $data)
    {
        return $this->getProvider($code)->refund($data);
    }

    public function getOnlineProviders()
    {
        return array_filter($this->providers, function ($provider) {
            return $provider->isOnline();
        });
    }

    public function getOfflineProviders()
    {
        return array_filter($this->providers, function ($provider) {
            return !$provider->isOnline();
        });
    }
}
