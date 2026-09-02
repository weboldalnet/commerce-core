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

    /**
     * A TELEPÍTETT szállítási integrációk (kód => metaadat), függetlenül attól,
     * hogy éppen aktívak-e.
     *
     * A $providers csak az aktív példányokat tartalmazza, így abból nem derülne
     * ki, mi közül lehet egyáltalán választani – és egy kikapcsolt modult nem
     * lehetne az adminból visszakapcsolni, mert meg sem jelenne.
     *
     * Metaadatok: name, settings_url (admin beállítóoldal), kind (lásd lent).
     */
    protected $available = [];

    /**
     * Szállítási mód jellege. A webshop pénztárában ez alapján csoportosulnak
     * a módok (házhoz szállítás / csomagpont / személyes átvétel).
     */
    const KIND_HOME_DELIVERY = 'home_delivery';
    const KIND_PARCEL_SHOP = 'parcel_shop';
    const KIND_PICKUP = 'pickup';
    const KIND_MANUAL = 'manual';

    /**
     * Telepített szállítási integráció bejelentése.
     * A csomagok a service providerükben, feltétel nélkül hívják.
     */
    public function registerAvailable($code, array $meta = [])
    {
        $this->available[$code] = array_merge([
            'name' => $code,
            'settings_url' => null,
            'kind' => self::KIND_HOME_DELIVERY,
        ], $meta);
    }

    /**
     * Minden telepített szállítási integráció (kód => metaadat).
     *
     * A 'builtin' azt jelzi, hogy a commerce-core saját providere (nem külön
     * telepített csomag), az 'active' pedig hogy be van-e kapcsolva.
     */
    public function getAvailableProviders()
    {
        $available = [];

        foreach ($this->available as $code => $meta) {
            $available[$code] = array_merge($meta, ['builtin' => false]);
        }

        foreach ($this->providers as $code => $provider) {
            if (isset($available[$code])) {
                continue;
            }

            $providerConfig = config('commerce-core.shipping.providers.' . $code, []);

            $available[$code] = [
                'name' => $providerConfig['name']
                    ?? (method_exists($provider, 'getName') ? $provider->getName() : $code),
                'settings_url' => null,
                'kind' => self::resolveKind($code, $providerConfig, $provider),
                'builtin' => true,
            ];
        }

        foreach ($available as $code => $meta) {
            $available[$code]['active'] = isset($this->providers[$code]);
        }

        return $available;
    }

    /**
     * Egy szállítási mód jellegének meghatározása.
     *
     * Sorrend: a provider saját getKind() metódusa, majd a config 'kind' kulcsa,
     * végül a kód alapján történő felismerés – így a régebbi, ezt nem ismerő
     * providerek is a helyes csoportba kerülnek.
     */
    public static function resolveKind($code, array $providerConfig = [], $provider = null)
    {
        if ($provider && method_exists($provider, 'getKind')) {
            $kind = $provider->getKind();
            if ($kind) {
                return $kind;
            }
        }

        if (!empty($providerConfig['kind'])) {
            return $providerConfig['kind'];
        }

        if ($code === self::KIND_PICKUP) {
            return self::KIND_PICKUP;
        }

        if ($code === self::KIND_MANUAL) {
            return self::KIND_MANUAL;
        }

        foreach (['parcel', 'locker', 'point', 'shop'] as $needle) {
            if (strpos($code, $needle) !== false) {
                return self::KIND_PARCEL_SHOP;
            }
        }

        return self::KIND_HOME_DELIVERY;
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
