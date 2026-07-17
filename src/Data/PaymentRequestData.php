<?php

namespace Weboldalnet\CommerceCore\Data;

class PaymentRequestData
{
    public $orderId;
    public $orderNumber;
    public $amount;
    public $currency;
    public $paymentMethod;
    public $customerName;
    public $customerEmail;
    public $customerPhone;
    public $customerData;
    public $billingData;
    public $shippingData;
    public $items;
    public $returnUrl;
    public $callbackUrl;
    public $language;
    public $timeout;
    public $extra;

    public function __construct(array $data = [])
    {
        $this->orderId = $data['order_id'] ?? null;
        $this->orderNumber = $data['order_number'] ?? null;
        $this->amount = $data['amount'] ?? 0;
        $this->currency = $data['currency'] ?? 'HUF';
        $this->paymentMethod = $data['payment_method'] ?? null;
        $this->customerName = $data['customer_name'] ?? ($data['customer']['name'] ?? null);
        $this->customerEmail = $data['customer_email'] ?? ($data['customer']['email'] ?? null);
        $this->customerPhone = $data['customer_phone'] ?? ($data['customer']['phone'] ?? null);
        $this->customerData = $data['customer'] ?? null;
        $this->billingData = $data['billing_data'] ?? ($data['billing_address'] ?? null);
        $this->shippingData = $data['shipping_data'] ?? ($data['shipping_address'] ?? null);
        $this->items = $data['items'] ?? [];
        $this->returnUrl = $data['return_url'] ?? null;
        $this->callbackUrl = $data['callback_url'] ?? null;
        $this->language = $data['language'] ?? 'HU';
        $this->timeout = $data['timeout'] ?? 1800;
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
            'order_number' => $this->orderNumber,
            'amount' => $this->amount,
            'currency' => $this->currency,
            'payment_method' => $this->paymentMethod,
            'customer_name' => $this->customerName,
            'customer_email' => $this->customerEmail,
            'customer_phone' => $this->customerPhone,
            'customer' => $this->customerData,
            'billing_data' => $this->billingData,
            'shipping_data' => $this->shippingData,
            'items' => $this->items,
            'return_url' => $this->returnUrl,
            'callback_url' => $this->callbackUrl,
            'language' => $this->language,
            'timeout' => $this->timeout,
            'extra' => $this->extra,
        ];
    }
}
