<?php

namespace Weboldalnet\CommerceCore\Data;

class PaymentRefundResult
{
    public $success;
    public $status;
    public $provider;
    public $transactionId;
    public $providerTransactionId;
    public $amount;
    public $currency;
    public $message;
    public $rawResponse;

    public function __construct(array $data = [])
    {
        $this->success = $data['success'] ?? false;
        $this->status = $data['status'] ?? null;
        $this->provider = $data['provider'] ?? null;
        $this->transactionId = $data['transaction_id'] ?? null;
        $this->providerTransactionId = $data['provider_transaction_id'] ?? null;
        $this->amount = $data['amount'] ?? null;
        $this->currency = $data['currency'] ?? null;
        $this->message = $data['message'] ?? null;
        $this->rawResponse = $data['raw_response'] ?? null;
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
            'amount' => $this->amount,
            'currency' => $this->currency,
            'message' => $this->message,
            'raw_response' => $this->rawResponse,
        ];
    }
}
