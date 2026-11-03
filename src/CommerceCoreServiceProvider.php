<?php

namespace Weboldalnet\CommerceCore;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\ServiceProvider;
use Weboldalnet\CommerceCore\Console\ReconcilePaymentsCommand;
use Weboldalnet\CommerceCore\Managers\InvoiceManager;
use Weboldalnet\CommerceCore\Managers\PaymentManager;
use Weboldalnet\CommerceCore\Managers\ShippingManager;
use Weboldalnet\CommerceCore\Services\CommerceOrderProcessor;
use Weboldalnet\CommerceCore\Services\PaymentCallbackProcessor;
use Weboldalnet\CommerceCore\Services\ProviderLogger;
use Weboldalnet\CommerceCore\Services\InvoiceService;
use Weboldalnet\CommerceCore\Services\ShippingService;
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

        // Fizetés-egyeztetés a Laravel ütemezőjében: a szerveren elég a szokásos `schedule:run` cron, külön sor nem kell.
        // Kikapcsolás: COMMERCE_RECONCILE_SCHEDULE= (üres) vagy false.
        $this->callAfterResolving(Schedule::class, function (Schedule $schedule) {
            $frequency = config('commerce-core.reconcile.schedule');
            if (!$frequency) {
                return;
            }

            $event = $schedule->command('commerce:reconcile-payments')->withoutOverlapping(30);

            // metódusnév (everyFifteenMinutes, hourly …) vagy cron-kifejezés (*/15 * * * *)
            if (str_contains((string) $frequency, ' ')) {
                $event->cron((string) $frequency);
            } elseif (method_exists($event, (string) $frequency)) {
                $event->{$frequency}();
            } else {
                $event->everyFifteenMinutes();
            }
        });
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

        // InvoiceService singleton
        $this->app->singleton(InvoiceService::class, function ($app) {
            return new InvoiceService(
                $app->make(InvoiceManager::class)
            );
        });

        // ShippingManager singleton
        $this->app->singleton(ShippingManager::class, function ($app) {
            return new ShippingManager();
        });

        // ShippingService singleton (az InvoiceService párja a szállítási oldalon)
        $this->app->singleton(ShippingService::class, function ($app) {
            return new ShippingService(
                $app->make(ShippingManager::class)
            );
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

        $this->commands([
            ReconcilePaymentsCommand::class,
        ]);
    }
}
