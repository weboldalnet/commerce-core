<?php

namespace Weboldalnet\CommerceCore\Services;

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

        $providerTransactionId = $result->providerTransactionId;
        $transactionId = $result->transactionId;
        $orderId = $result->orderId;
        $newStatus = $result->status;

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

        // Státusz frissítés és event küldés
        if ($result->success && $newStatus === PaymentStatus::PAID) {
            $transaction->markAsPaid();
            event(new PaymentSucceeded($transaction->order_id, $providerCode, $transaction, $result));
        } elseif ($newStatus === PaymentStatus::CANCELLED) {
            $transaction->markAsCancelled();
            event(new PaymentCancelled($transaction->order_id, $providerCode, $transaction, $result));
        } elseif ($newStatus === PaymentStatus::FAILED || !$result->success) {
            $transaction->markAsFailed();
            event(new PaymentFailed($transaction->order_id, $providerCode, $transaction, $result));
        }

        return [
            'skipped' => false,
            'transaction' => $transaction,
            'result' => $result,
        ];
    }
}
