<?php

namespace Weboldalnet\CommerceCore;

use Illuminate\Support\ServiceProvider;
use Weboldalnet\CommerceCore\Managers\InvoiceManager;
use Weboldalnet\CommerceCore\Managers\PaymentManager;
use Weboldalnet\CommerceCore\Managers\ShippingManager;
use Weboldalnet\CommerceCore\Services\CommerceOrderProcessor;
use Weboldalnet\CommerceCore\Services\PaymentCallbackProcessor;
use Weboldalnet\CommerceCore\Services\ProviderLogger;
use Weboldalnet\CommerceCore\Support\PackageHelper;

class CommerceCoreServiceProvider extends ServiceProvider
{
    public function boot()
    {
        // Route-ok betöltése
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');

        // Migrációk betöltése
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        // Config publikálhatóvá tétele
        $this->publishes([
            __DIR__.'/../config/commerce-core.php' => config_path('commerce-core.php'),
        ], PackageHelper::PACKAGE_PREFIX . '-config');

        // Migrációk publikálhatóvá tétele
        $this->publishes([
            __DIR__.'/../database/migrations' => database_path('migrations'),
        ], PackageHelper::PACKAGE_PREFIX . '-migrations');

        // Összes publish egy tagbe
        $this->publishes([
            __DIR__.'/../config/commerce-core.php' => config_path('commerce-core.php'),
            __DIR__.'/../database/migrations' => database_path('migrations'),
        ], PackageHelper::PACKAGE_PREFIX . '-all');
    }

    public function register()
    {
        // Config merge
        $this->mergeConfigFrom(__DIR__.'/../config/commerce-core.php', 'commerce-core');

        // PaymentManager singleton
        $this->app->singleton(PaymentManager::class, function ($app) {
            return new PaymentManager();
        });

        // InvoiceManager singleton
        $this->app->singleton(InvoiceManager::class, function ($app) {
            return new InvoiceManager();
        });

        // ShippingManager singleton
        $this->app->singleton(ShippingManager::class, function ($app) {
            return new ShippingManager();
        });

        // ProviderLogger singleton
        $this->app->singleton(ProviderLogger::class, function ($app) {
            return new ProviderLogger();
        });

        // PaymentCallbackProcessor singleton
        $this->app->singleton(PaymentCallbackProcessor::class, function ($app) {
            return new PaymentCallbackProcessor(
                $app->make(PaymentManager::class)
            );
        });

        // CommerceOrderProcessor singleton
        $this->app->singleton(CommerceOrderProcessor::class, function ($app) {
            return new CommerceOrderProcessor(
                $app->make(PaymentManager::class)
            );
        });
    }
}
