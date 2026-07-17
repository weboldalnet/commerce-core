<?php

namespace Weboldalnet\CommerceCore\Data;

class PaymentCallbackResult
{
    public $success;
    public $status;
    public $provider;
    public $transactionId;
    public $providerTransactionId;
    public $orderId;
    public $amount;
    public $currency;
    public $message;
    public $rawPayload;

    public function __construct(array $data = [])
    {
        $this->success = $data['success'] ?? false;
        $this->status = $data['status'] ?? null;
        $this->provider = $data['provider'] ?? null;
        $this->transactionId = $data['transaction_id'] ?? null;
        $this->providerTransactionId = $data['provider_transaction_id'] ?? null;
        $this->orderId = $data['order_id'] ?? null;
        $this->amount = $data['amount'] ?? null;
        $this->currency = $data['currency'] ?? null;
        $this->message = $data['message'] ?? null;
        $this->rawPayload = $data['raw_payload'] ?? null;
    }

    public static function fromArray(array $data)
    {
        return new static($data);
    }

    public static function success(array $data = [])
    {
        $data['success'] = true;
        return new static($data);
    }

    public static function failure(array $data = [])
    {
        $data['success'] = false;
        return new static($data);
    }

    public function toArray()
    {
        return [
            'success' => $this->success,
            'status' => $this->status,
            'provider' => $this->provider,
            'transaction_id' => $this->transactionId,
            'provider_transaction_id' => $this->providerTransactionId,
            'order_id' => $this->orderId,
            'amount' => $this->amount,
            'currency' => $this->currency,
            'message' => $this->message,
            'raw_payload' => $this->rawPayload,
        ];
    }
}
