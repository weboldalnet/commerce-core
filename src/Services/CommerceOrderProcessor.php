<?php

namespace Weboldalnet\CommerceCore\Services;

use Weboldalnet\CommerceCore\Data\PaymentRequestData;
use Weboldalnet\CommerceCore\Events\PaymentStarted;
use Weboldalnet\CommerceCore\Managers\PaymentManager;
use Weboldalnet\CommerceCore\Models\PaymentTransaction;
use Weboldalnet\CommerceCore\Status\OrderStatus;
use Weboldalnet\CommerceCore\Status\PaymentStatus;

class CommerceOrderProcessor
{
    protected $paymentManager;

    public function __construct(PaymentManager $paymentManager)
    {
        $this->paymentManager = $paymentManager;
    }

    /**
     * Rendelés feldolgozása: fizetési folyamat elindítása.
     * Nem függ konkrét webshop order modeltől, array/DTO alapú inputot vár.
     *
     * @param array $orderData Rendelési adatok:
     *   - order_id
     *   - payment_method (provider code)
     *   - amount
     *   - currency
     *   - customer_name, customer_email, customer_phone
     *   - billing_data, shipping_data
     *   - return_url, callback_url
     *   - extra
     * @return array
     */
    public function process(array $orderData)
    {
        $paymentMethod = $orderData['payment_method'] ?? config('commerce-core.payments.default');
        $orderId = $orderData['order_id'] ?? null;

        try {
            $provider = $this->paymentManager->getProvider($paymentMethod);
        } catch (\Exception $e) {
            return [
                'success' => false,
                'requiresRedirect' => false,
                'redirectUrl' => null,
                'paymentStatus' => PaymentStatus::FAILED,
                'orderStatusSuggestion' => OrderStatus::FAILED,
                'transactionId' => null,
                'message' => 'Fizetési provider nem elérhető: ' . $e->getMessage(),
                'rawResult' => null,
            ];
        }

        $requestData = PaymentRequestData::fromArray($orderData);

        try {
            $result = $provider->createPayment($requestData);
        } catch (\Exception $e) {
            return [
                'success' => false,
                'requiresRedirect' => false,
                'redirectUrl' => null,
                'paymentStatus' => PaymentStatus::FAILED,
                'orderStatusSuggestion' => OrderStatus::FAILED,
                'transactionId' => null,
                'message' => 'Fizetés indítása sikertelen: ' . $e->getMessage(),
                'rawResult' => null,
            ];
        }

        // Tranzakció mentése
        $transaction = null;
        if ($result->success || $result->status) {
            $transaction = PaymentTransaction::create([
                'order_id' => $orderId,
                'provider' => $paymentMethod,
                'payment_method' => $paymentMethod,
                'transaction_id' => $result->transactionId,
                'provider_transaction_id' => $result->providerTransactionId,
                'amount' => $orderData['amount'] ?? 0,
                'currency' => $orderData['currency'] ?? 'HUF',
                'status' => $result->status ?? PaymentStatus::PENDING,
                'request_payload' => $requestData->toArray(),
                'response_payload' => $result->toArray(),
            ]);
        }

        $requiresRedirect = $provider->isOnline() && !empty($result->redirectUrl);

        // Rendelés státusz javaslat
        if ($requiresRedirect) {
            $orderStatusSuggestion = OrderStatus::PENDING;
        } elseif ($result->success) {
            $orderStatusSuggestion = OrderStatus::PROCESSING;
        } else {
            $orderStatusSuggestion = OrderStatus::FAILED;
        }

        if ($result->success) {
            event(new PaymentStarted($orderId, $paymentMethod, $transaction ? $transaction->id : null, $result));
        }

        return [
            'success' => $result->success,
            'requiresRedirect' => $requiresRedirect,
            'redirectUrl' => $result->redirectUrl,
            'paymentStatus' => $result->status ?? PaymentStatus::PENDING,
            'orderStatusSuggestion' => $orderStatusSuggestion,
            'transactionId' => $transaction ? $transaction->id : null,
            'message' => $result->message,
            'rawResult' => $result,
        ];
    }
}
