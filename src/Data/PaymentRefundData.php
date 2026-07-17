<?php

namespace Weboldalnet\CommerceCore\Data;

class PaymentRefundData
{
    public $transactionId;
    public $providerTransactionId;
    public $orderId;
    public $amount;
    public $currency;
    public $reason;
    public $extra;

    public function __construct(array $data = [])
    {
        $this->transactionId = $data['transaction_id'] ?? null;
        $this->providerTransactionId = $data['provider_transaction_id'] ?? null;
        $this->orderId = $data['order_id'] ?? null;
        $this->amount = $data['amount'] ?? null;
        $this->currency = $data['currency'] ?? 'HUF';
        $this->reason = $data['reason'] ?? null;
        $this->extra = $data['extra'] ?? [];
    }

    public static function fromArray(array $data)
    {
        return new static($data);
    }

    public function toArray()
    {
        return [
            'transaction_id' => $this->transactionId,
            'provider_transaction_id' => $this->providerTransactionId,
            'order_id' => $this->orderId,
            'amount' => $this->amount,
            'currency' => $this->currency,
            'reason' => $this->reason,
            'extra' => $this->extra,
        ];
    }
}
