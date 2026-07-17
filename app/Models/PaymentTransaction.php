<?php

namespace Weboldalnet\CommerceCore\Models;

use Illuminate\Database\Eloquent\Model;
use Weboldalnet\CommerceCore\Status\PaymentStatus;

class PaymentTransaction extends Model
{
    protected $table = 'public.commerce_payment_transactions';

    protected $fillable = [
        'order_id',
        'provider',
        'payment_method',
        'transaction_id',
        'provider_transaction_id',
        'amount',
        'currency',
        'status',
        'request_payload',
        'response_payload',
        'callback_payload',
        'paid_at',
        'failed_at',
        'cancelled_at',
        'refunded_at',
    ];

    protected $casts = [
        'amount' => 'float',
        'request_payload' => 'array',
        'response_payload' => 'array',
        'callback_payload' => 'array',
        'paid_at' => 'datetime',
        'failed_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'refunded_at' => 'datetime',
    ];

    public function isPaid()
    {
        return $this->status === PaymentStatus::PAID;
    }

    public function isFailed()
    {
        return $this->status === PaymentStatus::FAILED;
    }

    public function isCancelled()
    {
        return $this->status === PaymentStatus::CANCELLED;
    }

    public function isRefunded()
    {
        return $this->status === PaymentStatus::REFUNDED;
    }

    public function isPending()
    {
        return $this->status === PaymentStatus::PENDING;
    }

    public function markAsPaid()
    {
        $this->update(['status' => PaymentStatus::PAID, 'paid_at' => now()]);
    }

    public function markAsFailed()
    {
        $this->update(['status' => PaymentStatus::FAILED, 'failed_at' => now()]);
    }

    public function markAsCancelled()
    {
        $this->update(['status' => PaymentStatus::CANCELLED, 'cancelled_at' => now()]);
    }

    public function markAsRefunded()
    {
        $this->update(['status' => PaymentStatus::REFUNDED, 'refunded_at' => now()]);
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

    public function scopePaid($query)
    {
        return $query->where('status', PaymentStatus::PAID);
    }

    public function scopeFailed($query)
    {
        return $query->where('status', PaymentStatus::FAILED);
    }

    public function getStatusLabelAttribute()
    {
        return PaymentStatus::label($this->status);
    }
}
