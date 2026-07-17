<?php

namespace Weboldalnet\CommerceCore\Models;

use Illuminate\Database\Eloquent\Model;
use Weboldalnet\CommerceCore\Status\ShippingStatus;

class Shipment extends Model
{
    protected $table = 'public.commerce_shipments';

    protected $fillable = [
        'order_id',
        'provider',
        'shipping_method',
        'tracking_number',
        'tracking_url',
        'label_path',
        'status',
        'request_payload',
        'response_payload',
    ];

    protected $casts = [
        'request_payload' => 'array',
        'response_payload' => 'array',
    ];

    public function isShipped()
    {
        return $this->status === ShippingStatus::SHIPPED;
    }

    public function isDelivered()
    {
        return $this->status === ShippingStatus::DELIVERED;
    }

    public function isFailed()
    {
        return $this->status === ShippingStatus::FAILED;
    }

    public function isPending()
    {
        return $this->status === ShippingStatus::PENDING;
    }

    public function isPrepared()
    {
        return $this->status === ShippingStatus::PREPARED;
    }

    public function markAsShipped()
    {
        $this->update(['status' => ShippingStatus::SHIPPED]);
    }

    public function markAsDelivered()
    {
        $this->update(['status' => ShippingStatus::DELIVERED]);
    }

    public function markAsFailed()
    {
        $this->update(['status' => ShippingStatus::FAILED]);
    }

    public function markAsPrepared()
    {
        $this->update(['status' => ShippingStatus::PREPARED]);
    }

    public function scopeByOrder($query, $orderId)
    {
        return $query->where('order_id', $orderId);
    }

    public function scopeByProvider($query, $provider)
    {
        return $query->where('provider', $provider);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeShipped($query)
    {
        return $query->where('status', ShippingStatus::SHIPPED);
    }

    public function getStatusLabelAttribute()
    {
        return ShippingStatus::label($this->status);
    }
}
