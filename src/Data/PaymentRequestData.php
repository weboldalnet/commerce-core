<?php

namespace Weboldalnet\CommerceCore\Data;

class PaymentRequestData
{
    public $orderId;
    public $amount;
    public $currency;
    public $paymentMethod;
    public $customerName;
    public $customerEmail;
    public $customerPhone;
    public $billingData;
    public $shippingData;
    public $returnUrl;
    public $callbackUrl;
    public $language;
    public $extra;

    public function __construct(array $data = [])
    {
        $this->orderId = $data['order_id'] ?? null;
        $this->amount = $data['amount'] ?? 0;
        $this->currency = $data['currency'] ?? 'HUF';
        $this->paymentMethod = $data['payment_method'] ?? null;
        $this->customerName = $data['customer_name'] ?? null;
        $this->customerEmail = $data['customer_email'] ?? null;
        $this->customerPhone = $data['customer_phone'] ?? null;
        $this->billingData = $data['billing_data'] ?? null;
        $this->shippingData = $data['shipping_data'] ?? null;
        $this->returnUrl = $data['return_url'] ?? null;
        $this->callbackUrl = $data['callback_url'] ?? null;
        $this->language = $data['language'] ?? 'HU';
        $this->extra = $data['extra'] ?? [];
    }

    public static function fromArray(array $data)
    {
        return new static($data);
    }

    public function toArray()
    {
        return [
            'order_id' => $this->orderId,
            'amount' => $this->amount,
            'currency' => $this->currency,
            'payment_method' => $this->paymentMethod,
            'customer_name' => $this->customerName,
            'customer_email' => $this->customerEmail,
            'customer_phone' => $this->customerPhone,
            'billing_data' => $this->billingData,
            'shipping_data' => $this->shippingData,
            'return_url' => $this->returnUrl,
            'callback_url' => $this->callbackUrl,
            'language' => $this->language,
            'extra' => $this->extra,
        ];
    }
}
