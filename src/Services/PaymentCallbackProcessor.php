<?php

namespace Weboldalnet\CommerceCore\Services;

use Illuminate\Support\Facades\Log;
use Weboldalnet\CommerceCore\Events\PaymentCancelled;
use Weboldalnet\CommerceCore\Events\PaymentFailed;
use Weboldalnet\CommerceCore\Events\PaymentSucceeded;
use Weboldalnet\CommerceCore\Managers\PaymentManager;
use Weboldalnet\CommerceCore\Models\PaymentTransaction;
use Weboldalnet\CommerceCore\Status\PaymentStatus;

class PaymentCallbackProcessor
{
    protected $paymentManager;

    public function __construct(PaymentManager $paymentManager)
    {
        $this->paymentManager = $paymentManager;
    }

    /**
     * Callback feldolgozása idempotens módon.
     * Egy adott provider_transaction_id-val rendelkező callback csak egyszer kerül feldolgozásra.
     *
     * @param string $providerCode
     * @param array $payload
     * @return array
     */
    public function process($providerCode, array $payload)
    {
        $provider = $this->paymentManager->getProvider($providerCode);
        $result = $provider->handleCallback($payload);

        return $this->processResult($providerCode, $result, $payload);
    }

    /**
     * Ugyanaz a feldolgozás, de már kész eredménnyel.
     *
     * Akkor kell, ha az állapot nem callbackből érkezik, hanem mi kérdezzük le a
     * providertől (pl. a fizetési eredmény oldal frissítése, amikor az IPN nem
     * ért el minket). Így a tranzakció frissítése, az idempotencia és az
     * eventek küldése egyetlen helyen marad – nem lesz belőle második,
     * eltérően viselkedő út.
     *
     * @param string $providerCode
     * @param PaymentCallbackResult $result
     * @param array $payload naplózásra kerülő nyers adat (opcionális)
     * @return array
     */
    public function processResult($providerCode, $result, array $payload = [])
    {
        $providerTransactionId = $result->providerTransactionId;
        $transactionId = $result->transactionId;
        $newStatus = $result->status;

        // Az order_id oszlop INTEGER. Több provider (pl. Barion) a saját azonosítójában
        // a rendelésszámot adja vissza ("20260901-AB12"), ami nem numerikus – azt nem írjuk be.
        $orderId = is_numeric($result->orderId) ? (int) $result->orderId : null;

        // Keressük a meglévő tranzakciót
        $transaction = null;
        if ($providerTransactionId) {
            $transaction = PaymentTransaction::where('provider', $providerCode)
                ->where('provider_transaction_id', $providerTransactionId)
                ->first();
        }
        if (!$transaction && $transactionId) {
            $transaction = PaymentTransaction::where('provider', $providerCode)
                ->where('transaction_id', $transactionId)
                ->first();
        }

        // Ha a provider egyáltalán nem tudta azonosítani a fizetést (nincs sem provider oldali,
        // sem saját tranzakció azonosító), nincs mit feldolgozni. Ilyen pl. amikor valaki a
        // visszatérési oldalt a provider query paraméterei nélkül nyitja meg (könyvjelző,
        // frissítés, vissza gomb). Ilyenkor NEM hozunk létre üres tranzakciót.
        if (!$transaction && !$providerTransactionId && !$transactionId) {
            return [
                'skipped' => true,
                'reason' => 'A callback nem tartalmazott azonosítható tranzakciót.',
                'transaction' => null,
                'result' => $result,
            ];
        }

        // Idempotencia ellenőrzés: ha már végállapotban van, ne dolgozzuk fel újra
        if ($transaction && in_array($transaction->status, [PaymentStatus::PAID, PaymentStatus::REFUNDED])) {
            return [
                'skipped' => true,
                'reason' => 'Transaction already in final state: ' . $transaction->status,
                'transaction' => $transaction,
                'result' => $result,
            ];
        }

        // Tranzakció létrehozása vagy frissítése
        if (!$transaction) {
            if (!$orderId) {
                // Ilyenkor a rendeléshez nem tudjuk hozzákötni a fizetést, így a webshop
                // státuszfrissítő listenerei sem futnak le – ez mindig kivizsgálandó.
                Log::warning('PaymentCallbackProcessor: callback érkezett ismeretlen tranzakcióra, rendelés azonosító nélkül.', [
                    'provider' => $providerCode,
                    'provider_transaction_id' => $providerTransactionId,
                    'transaction_id' => $transactionId,
                    'raw_order_id' => $result->orderId,
                ]);
            }

            $transaction = PaymentTransaction::create([
                'order_id' => $orderId,
                'provider' => $providerCode,
                'provider_transaction_id' => $providerTransactionId,
                'transaction_id' => $transactionId,
                'amount' => $result->amount ?? 0,
                'currency' => $result->currency ?? 'HUF',
                'status' => $newStatus ?? PaymentStatus::PENDING,
                'callback_payload' => $payload,
            ]);
        } else {
            $transaction->update([
                'callback_payload' => $payload,
                'status' => $newStatus ?? $transaction->status,
            ]);

            if ($orderId && !$transaction->order_id) {
                $transaction->update(['order_id' => $orderId]);
            }
        }

        // Státusz frissítés és event küldés.
        // FONTOS: csak valódi végállapotra küldünk eventet. A folyamatban lévő
        // (pending) fizetés nem sikertelen – ha a vásárló a fizetés befejezése előtt
        // tér vissza, vagy a provider köztes állapotról küld callbacket, a rendelést
        // nem szabad sikertelenre állítani.
        if ($result->success && $newStatus === PaymentStatus::PAID) {
            $transaction->markAsPaid();
            event(new PaymentSucceeded($transaction->order_id, $providerCode, $transaction, $result));
        } elseif ($newStatus === PaymentStatus::CANCELLED) {
            $transaction->markAsCancelled();
            event(new PaymentCancelled($transaction->order_id, $providerCode, $transaction, $result));
        } elseif ($newStatus === PaymentStatus::FAILED) {
            $transaction->markAsFailed();
            event(new PaymentFailed($transaction->order_id, $providerCode, $transaction, $result));
        } elseif (!$result->success && $newStatus !== PaymentStatus::PENDING) {
            // Nem tudtuk megállapítani az állapotot (pl. a provider lekérdezés hibázott).
            // Ez sem végállapot: a tranzakciót hagyjuk, csak naplózunk.
            Log::warning('PaymentCallbackProcessor: eldönthetetlen fizetési állapot, a tranzakció változatlan.', [
                'provider' => $providerCode,
                'transaction_id' => $transaction->id,
                'status' => $newStatus,
                'message' => $result->message ?? null,
            ]);
        }

        return [
            'skipped' => false,
            'transaction' => $transaction,
            'result' => $result,
        ];
    }
}
