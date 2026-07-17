<?php

namespace Weboldalnet\CommerceCore\Data;

class ShipmentRequestData
{
    public $orderId;
    public $orderNumber;
    public $shippingMethod;
    public $customerName;
    public $customerPhone;
    public $customerEmail;
    public $shippingData;
    public $items;
    public $weight;
    public $currency;
    public $extra;

    public function __construct(array $data = [])
    {
        $this->orderId = $data['order_id'] ?? null;
        $this->orderNumber = $data['order_number'] ?? null;
        $this->shippingMethod = $data['shipping_method'] ?? null;
        $this->customerName = $data['customer_name'] ?? null;
        $this->customerPhone = $data['customer_phone'] ?? null;
        $this->customerEmail = $data['customer_email'] ?? null;
        $this->shippingData = $data['shipping_data'] ?? null;
        $this->items = $data['items'] ?? [];
        $this->weight = $data['weight'] ?? null;
        $this->currency = $data['currency'] ?? 'HUF';
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
            'shipping_method' => $this->shippingMethod,
            'customer_name' => $this->customerName,
            'customer_phone' => $this->customerPhone,
            'customer_email' => $this->customerEmail,
            'shipping_data' => $this->shippingData,
            'items' => $this->items,
            'weight' => $this->weight,
            'currency' => $this->currency,
            'extra' => $this->extra,
        ];
    }
}
