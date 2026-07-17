<?php

namespace Weboldalnet\CommerceCore\Managers;

use Weboldalnet\CommerceCore\Contracts\InvoiceProviderInterface;
use Weboldalnet\CommerceCore\Data\InvoiceRequestData;
use Weboldalnet\CommerceCore\Exceptions\ProviderNotEnabledException;
use Weboldalnet\CommerceCore\Exceptions\ProviderNotFoundException;

class InvoiceManager
{
    protected $providers = [];

    public function __construct()
    {
        $this->loadProviders();
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
