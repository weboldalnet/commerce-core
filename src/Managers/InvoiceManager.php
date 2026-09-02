<?php

namespace Weboldalnet\CommerceCore\Managers;

use Weboldalnet\CommerceCore\Contracts\InvoiceProviderInterface;
use Weboldalnet\CommerceCore\Data\InvoiceRequestData;
use Weboldalnet\CommerceCore\Exceptions\ProviderNotEnabledException;
use Weboldalnet\CommerceCore\Exceptions\ProviderNotFoundException;

class InvoiceManager
{
    protected $providers = [];

    /**
     * A TELEPÍTETT számlázó integrációk (kód => metaadat), függetlenül attól,
     * hogy éppen aktívak-e.
     *
     * A $providers csak az aktív (regisztrált) példányokat tartalmazza, így
     * abból nem derülne ki, mi közül lehet egyáltalán választani. A webshop
     * beállítófelülete ebből a listából kínálja fel a számlázó programokat,
     * ezért egy új számlázó csomag bekötéséhez nem kell a webshophoz nyúlni.
     *
     * Metaadatok: name (megjelenő név), settings_url (admin beállítóoldal).
     */
    protected $available = [];

    public function __construct()
    {
        $this->loadProviders();
    }

    /**
     * Telepített számlázó integráció bejelentése.
     * A csomagok a service providerükben, feltétel nélkül hívják.
     */
    public function registerAvailable($code, array $meta = [])
    {
        $this->available[$code] = array_merge([
            'name' => $code,
            'settings_url' => null,
        ], $meta);
    }

    /**
     * Minden telepített számlázó integráció (kód => metaadat).
     *
     * Metaadatok:
     *   name         – megjelenő név
     *   settings_url – admin beállítóoldal (ha van)
     *   builtin      – a commerce-core saját providere (pl. manuális számlázás),
     *                  nem külön telepített integrációs csomag
     *   active       – be van-e kapcsolva (regisztrált példány van-e)
     */
    public function getAvailableProviders()
    {
        $available = [];

        foreach ($this->available as $code => $meta) {
            $available[$code] = array_merge($meta, ['builtin' => false]);
        }

        // A configból betöltött (beépített) providerek is választhatók legyenek,
        // akkor is, ha nem hívták rájuk a registerAvailable()-t.
        foreach ($this->providers as $code => $provider) {
            if (isset($available[$code])) {
                continue;
            }

            $providerConfig = config('commerce-core.invoice.providers.' . $code, []);

            $available[$code] = [
                'name' => $providerConfig['name']
                    ?? (method_exists($provider, 'getName') ? $provider->getName() : $code),
                'settings_url' => null,
                'builtin' => true,
            ];
        }

        foreach ($available as $code => $meta) {
            $available[$code]['active'] = isset($this->providers[$code]);
        }

        return $available;
    }

    protected function loadProviders()
    {
        $providerConfigs = config('commerce-core.invoice.providers', []);

        foreach ($providerConfigs as $code => $providerConfig) {
            if (empty($providerConfig['enabled'])) {
                continue;
            }
            $driverClass = $providerConfig['driver'] ?? null;
            if (!$driverClass || !class_exists($driverClass)) {
                continue;
            }
            $instance = new $driverClass();
            if (!($instance instanceof InvoiceProviderInterface)) {
                continue;
            }
            $this->providers[$code] = $instance;
        }
    }

    public function register($code, InvoiceProviderInterface $provider)
    {
        $this->providers[$code] = $provider;
    }

    public function getProvider($code)
    {
        if (!isset($this->providers[$code])) {
            $providerConfig = config('commerce-core.invoice.providers.' . $code);
            if (!$providerConfig) {
                throw new ProviderNotFoundException("Invoice provider '{$code}' nem található.");
            }
            throw new ProviderNotEnabledException("Invoice provider '{$code}' nincs engedélyezve.");
        }

        return $this->providers[$code];
    }

    public function getDefaultProvider()
    {
        $default = config('commerce-core.invoice.default');
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

    public function createInvoice($code, InvoiceRequestData $data)
    {
        return $this->getProvider($code)->createInvoice($data);
    }

    public function voidInvoice($code, $invoiceNumber)
    {
        return $this->getProvider($code)->voidInvoice($invoiceNumber);
    }

    public function downloadPdf($code, $invoiceNumber)
    {
        return $this->getProvider($code)->downloadPdf($invoiceNumber);
    }
}
