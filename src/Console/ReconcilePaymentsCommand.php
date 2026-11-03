<?php

namespace Weboldalnet\CommerceCore\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Weboldalnet\CommerceCore\Managers\PaymentManager;
use Weboldalnet\CommerceCore\Models\PaymentTransaction;
use Weboldalnet\CommerceCore\Services\PaymentCallbackProcessor;
use Weboldalnet\CommerceCore\Status\PaymentStatus;

/**
 * Fizetés-egyeztetés: a függő online tranzakciók állapotát lekérdezi a szolgáltatótól, és ugyanúgy feldolgozza, mintha
 * az értesítése (IPN / webhook) megérkezett volna.
 *
 * MIÉRT: ha a szolgáltató értesítése nem ér célba (szerverhiba, karbantartás, rossz titok), és a vásárló a fizetés után
 * nem tér vissza az oldalra, a rendelés „függőben” ragad — pedig a pénz megérkezett, és a visszaigazoló levél sem megy
 * ki. A lejárt / megszakított fizetéseket is ez zárja le (a SimplePay ezekről nem küld értesítést).
 *
 * Minden bekapcsolt online szolgáltatót néz, amelynek van `queryStatus()` metódusa (Barion, SimplePay, Stripe). A
 * friss tranzakciókhoz nem nyúl (a vásárló épp fizethet), a régiekhez sem (a szolgáltató már nem tartja őket).
 * Ütemezve: commerce-core.reconcile.schedule (alapból 15 percenként); kézzel: php artisan commerce:reconcile-payments
 */
class ReconcilePaymentsCommand extends Command
{
    protected $signature = 'commerce:reconcile-payments
        {--provider= : csak ez a fizetési mód (kód, pl. barion)}
        {--days= : ennyi napnál nem régebbi függő tranzakciók}
        {--min-age= : ennyi percnél frissebb tranzakciókhoz nem nyúl}';

    protected $description = 'A függő online fizetések állapotának lekérdezése a szolgáltatótól és feldolgozása (elmaradt értesítés pótlása)';

    public function handle(PaymentManager $payments, PaymentCallbackProcessor $processor): int
    {
        $days = max(1, (int) ($this->option('days') ?: config('commerce-core.reconcile.days', 2)));
        $minAge = max(0, (int) ($this->option('min-age') ?? config('commerce-core.reconcile.min_age_minutes', 10)));
        $limit = max(1, (int) config('commerce-core.reconcile.limit', 200));

        $providers = array_filter($payments->getOnlineProviders(), fn ($provider) => method_exists($provider, 'queryStatus'));
        if ($only = $this->option('provider')) {
            $providers = array_intersect_key($providers, [$only => true]);
        }
        if (!$providers) {
            $this->info('Nincs bekapcsolt, egyeztethető online fizetési mód — nincs teendő.');

            return self::SUCCESS;
        }

        $checked = $changed = $errors = 0;
        foreach ($providers as $code => $provider) {
            $pending = PaymentTransaction::query()
                ->where('provider', $code)
                ->whereIn('status', [PaymentStatus::PENDING, PaymentStatus::UNPAID])
                ->where(fn ($query) => $query->whereNotNull('provider_transaction_id')->orWhereNotNull('transaction_id'))
                ->where('created_at', '>=', now()->subDays($days))
                ->where('created_at', '<=', now()->subMinutes($minAge))
                ->orderBy('id')
                ->limit($limit)
                ->get();

            foreach ($pending as $transaction) {
                $checked++;
                try {
                    $result = $provider->queryStatus($transaction);
                    if (!$result || !$result->status || $result->status === PaymentStatus::PENDING) {
                        continue;
                    }
                    $processed = $processor->processResult($code, $result, ['source' => 'reconcile'] + (array) ($result->rawPayload ?? []));
                    if (empty($processed['skipped'])) {
                        $changed++;
                    }
                } catch (\Throwable $e) {
                    $errors++;
                    Log::warning('Fizetés-egyeztetés: a tranzakció lekérdezése sikertelen.', [
                        'provider' => $code,
                        'transaction' => $transaction->id,
                        'message' => $e->getMessage(),
                    ]);
                    $this->warn($code . ' #' . $transaction->id . ': ' . $e->getMessage());
                }
            }
        }

        $summary = $checked . ' függő tranzakció ellenőrizve, ' . $changed . ' állapota változott, ' . $errors . ' hiba.';
        $this->info($summary);
        if ($changed || $errors) {
            Log::info('commerce:reconcile-payments — ' . $summary);
        }

        return self::SUCCESS;
    }
}
