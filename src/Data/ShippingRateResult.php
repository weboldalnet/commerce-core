<?php

namespace Weboldalnet\CommerceCore\Data;

class ShippingRateResult
{
    public $success;
    public $provider;
    public $shippingMethod;
    public $rate;
    public $currency;
    public $isFree;
    public $message;
    public $extra;

    public function __construct(array $data = [])
    {
        $this->success = $data['success'] ?? false;
        $this->provider = $data['provider'] ?? null;
        $this->shippingMethod = $data['shipping_method'] ?? null;
        $this->rate = $data['rate'] ?? 0;
        $this->currency = $data['currency'] ?? 'HUF';
        $this->isFree = $data['is_free'] ?? false;
        $this->message = $data['message'] ?? null;
        $this->extra = $data['extra'] ?? [];
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
            'provider' => $this->provider,
            'shipping_method' => $this->shippingMethod,
            'rate' => $this->rate,
            'currency' => $this->currency,
            'is_free' => $this->isFree,
            'message' => $this->message,
            'extra' => $this->extra,
        ];
    }
}
