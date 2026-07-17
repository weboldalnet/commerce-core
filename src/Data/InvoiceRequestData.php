<?php

namespace Weboldalnet\CommerceCore\Data;

class InvoiceRequestData
{
    public $orderId;
    public $orderNumber;
    public $customerName;
    public $customerEmail;
    public $customerTaxNumber;
    public $billingData;
    public $items;
    public $grossTotal;
    public $currency;
    public $language;
    public $extra;

    public function __construct(array $data = [])
    {
        $this->orderId = $data['order_id'] ?? null;
        $this->orderNumber = $data['order_number'] ?? null;
        $this->customerName = $data['customer_name'] ?? null;
        $this->customerEmail = $data['customer_email'] ?? null;
        $this->customerTaxNumber = $data['customer_tax_number'] ?? null;
        $this->billingData = $data['billing_data'] ?? null;
        $this->items = $data['items'] ?? [];
        $this->grossTotal = $data['gross_total'] ?? 0;
        $this->currency = $data['currency'] ?? 'HUF';
        $this->language = $data['language'] ?? 'hu';
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
            'customer_name' => $this->customerName,
            'customer_email' => $this->customerEmail,
            'customer_tax_number' => $this->customerTaxNumber,
            'billing_data' => $this->billingData,
            'items' => $this->items,
            'gross_total' => $this->grossTotal,
            'currency' => $this->currency,
            'language' => $this->language,
            'extra' => $this->extra,
        ];
    }
}
