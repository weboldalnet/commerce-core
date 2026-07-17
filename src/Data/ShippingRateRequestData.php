<?php

namespace Weboldalnet\CommerceCore\Data;

class ShippingRateRequestData
{
    public $orderId;
    public $shippingMethod;
    public $cartTotal;
    public $currency;
    public $weight;
    public $items;
    public $shippingData;
    public $extra;

    public function __construct(array $data = [])
    {
        $this->orderId = $data['order_id'] ?? null;
        $this->shippingMethod = $data['shipping_method'] ?? null;
        $this->cartTotal = $data['cart_total'] ?? 0;
        $this->currency = $data['currency'] ?? 'HUF';
        $this->weight = $data['weight'] ?? null;
        $this->items = $data['items'] ?? [];
        $this->shippingData = $data['shipping_data'] ?? null;
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
            'shipping_method' => $this->shippingMethod,
            'cart_total' => $this->cartTotal,
            'currency' => $this->currency,
            'weight' => $this->weight,
            'items' => $this->items,
            'shipping_data' => $this->shippingData,
            'extra' => $this->extra,
        ];
    }
}
