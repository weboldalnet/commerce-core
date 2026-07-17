<?php

namespace Weboldalnet\CommerceCore\Models;

use Illuminate\Database\Eloquent\Model;

class ProviderLog extends Model
{
    protected $table = 'public.commerce_provider_logs';

    protected $fillable = [
        'provider_type',
        'provider',
        'order_id',
        'direction',
        'endpoint',
        'request_payload',
        'response_payload',
        'status_code',
        'is_success',
        'error_message',
    ];

    protected $casts = [
        'request_payload' => 'array',
        'response_payload' => 'array',
        'status_code' => 'integer',
        'is_success' => 'boolean',
    ];

    public function scopeByProvider($query, $provider)
    {
        return $query->where('provider', $provider);
    }

    public function scopeByProviderType($query, $type)
    {
        return $query->where('provider_type', $type);
    }

    public function scopeByOrder($query, $orderId)
    {
        return $query->where('order_id', $orderId);
    }

    public function scopeSuccessful($query)
    {
        return $query->where('is_success', true);
    }

    public function scopeFailed($query)
    {
        return $query->where('is_success', false);
    }

    public function scopePayment($query)
    {
        return $query->where('provider_type', 'payment');
    }

    public function scopeInvoice($query)
    {
        return $query->where('provider_type', 'invoice');
    }

    public function scopeShipping($query)
    {
        return $query->where('provider_type', 'shipping');
    }
}
