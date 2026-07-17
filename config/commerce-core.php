<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Fizetési beállítások
    |--------------------------------------------------------------------------
    */
    'payments' => [
        'default' => env('COMMERCE_PAYMENT_DEFAULT', 'cod'),

        'enabled' => explode(',', env('COMMERCE_PAYMENT_ENABLED', 'cod,bank_transfer,manual')),

        'providers' => [
            'cod' => [
                'driver' => \Weboldalnet\CommerceCore\Providers\Payment\CashOnDeliveryPaymentProvider::class,
                'name' => 'Utánvét',
                'enabled' => true,
            ],
            'bank_transfer' => [
                'driver' => \Weboldalnet\CommerceCore\Providers\Payment\BankTransferPaymentProvider::class,
                'name' => 'Banki átutalás',
                'enabled' => true,
            ],
            'manual' => [
                'driver' => \Weboldalnet\CommerceCore\Providers\Payment\ManualPaymentProvider::class,
                'name' => 'Manuális fizetés',
                'enabled' => true,
            ],
            // Külső provider placeholder (pl. SimplePay, Stripe stb.)
            // 'simplepay' => [
            //     'driver' => \Weboldalnet\SimplePayProvider\SimplePayPaymentProvider::class,
            //     'name' => 'SimplePay',
            //     'enabled' => false,
            // ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Számlázási beállítások
    |--------------------------------------------------------------------------
    */
    'invoice' => [
        'default' => env('COMMERCE_INVOICE_DEFAULT', 'manual'),

        'providers' => [
            'manual' => [
                'driver' => \Weboldalnet\CommerceCore\Providers\Invoice\ManualInvoiceProvider::class,
                'name' => 'Manuális számlázás',
                'enabled' => true,
            ],
            // Külső provider placeholder (pl. Számlázz.hu, Billingo stb.)
            // 'szamlazz' => [
            //     'driver' => \Weboldalnet\SzamlazzProvider\SzamlazzInvoiceProvider::class,
            //     'name' => 'Számlázz.hu',
            //     'enabled' => false,
            // ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Szállítási beállítások
    |--------------------------------------------------------------------------
    */
    'shipping' => [
        'default' => env('COMMERCE_SHIPPING_DEFAULT', 'manual'),

        'enabled' => explode(',', env('COMMERCE_SHIPPING_ENABLED', 'manual,pickup,flat_rate')),

        'providers' => [
            'manual' => [
                'driver' => \Weboldalnet\CommerceCore\Providers\Shipping\ManualShippingProvider::class,
                'name' => 'Manuális szállítás',
                'enabled' => true,
            ],
            'pickup' => [
                'driver' => \Weboldalnet\CommerceCore\Providers\Shipping\PickupShippingProvider::class,
                'name' => 'Személyes átvétel',
                'enabled' => true,
            ],
            'flat_rate' => [
                'driver' => \Weboldalnet\CommerceCore\Providers\Shipping\FlatRateShippingProvider::class,
                'name' => 'Fix szállítási díj',
                'enabled' => true,
                'rate' => env('COMMERCE_FLAT_RATE_SHIPPING', 1490),
                'currency' => env('COMMERCE_FLAT_RATE_CURRENCY', 'HUF'),
                'free_above' => env('COMMERCE_FLAT_RATE_FREE_ABOVE', null),
            ],
            // Külső provider placeholder (pl. GLS, MPL stb.)
            // 'gls' => [
            //     'driver' => \Weboldalnet\GlsProvider\GlsShippingProvider::class,
            //     'name' => 'GLS',
            //     'enabled' => false,
            // ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Provider API logolás
    |--------------------------------------------------------------------------
    */
    'provider_logs' => [
        'enabled' => env('COMMERCE_PROVIDER_LOGS_ENABLED', true),
        'sanitize_keys' => [
            'token', 'secret', 'password', 'api_key', 'merchant_key',
            'signature', 'card_number', 'cvv', 'cvc', 'expiry',
            'pan', 'card_data', 'private_key', 'auth_token',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Automatikus számlázás
    |--------------------------------------------------------------------------
    */
    'auto_invoice' => [
        'enabled' => env('COMMERCE_AUTO_INVOICE_ENABLED', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Automatikus szállítás
    |--------------------------------------------------------------------------
    */
    'auto_shipping' => [
        'enabled' => env('COMMERCE_AUTO_SHIPPING_ENABLED', false),
    ],

];
