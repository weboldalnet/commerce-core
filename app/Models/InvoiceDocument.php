<?php

namespace Weboldalnet\CommerceCore\Models;

use Illuminate\Database\Eloquent\Model;
use Weboldalnet\CommerceCore\Status\InvoiceStatus;

class InvoiceDocument extends Model
{
    protected $table = 'public.commerce_invoice_documents';

    protected $fillable = [
        'order_id',
        'provider',
        'invoice_number',
        'invoice_id',
        'status',
        'gross_total',
        'currency',
        'pdf_path',
        'request_payload',
        'response_payload',
        'error_code',
        'error_message',
        'issued_at',
        'voided_at',
    ];

    protected $casts = [
        'gross_total' => 'float',
        'request_payload' => 'array',
        'response_payload' => 'array',
        'issued_at' => 'datetime',
        'voided_at' => 'datetime',
    ];

    /**
     * A rendelés modell osztálya. A commerce-core szándékosan nem függ a webshop
     * csomagtól: a modellt a commerce-core.order_model configból vesszük, és csak
     * végszükségben esünk vissza a webshop-ai-default modelljére.
     */
    public static function orderModelClass()
    {
        $configured = config('commerce-core.order_model');
        if ($configured && class_exists($configured)) {
            return $configured;
        }

        $fallback = 'Weboldalnet\\WebshopAiDefault\\Models\\WebshopOrder';

        return class_exists($fallback) ? $fallback : null;
    }

    /**
     * A bizonylathoz tartozó rendelés.
     * Ha nincs rendelés modell, üres, de érvényes relációt adunk vissza, hogy a
     * with() / whereHas() hívások ne hasaljanak el.
     */
    public function order()
    {
        $orderModel = self::orderModelClass();

        if (!$orderModel) {
            return $this->belongsTo(self::class, 'order_id')->whereRaw('1 = 0');
        }

        return $this->belongsTo($orderModel, 'order_id');
    }

    public function isInvoiced()
    {
        return $this->status === InvoiceStatus::INVOICED;
    }

    public function isVoided()
    {
        return $this->status === InvoiceStatus::VOIDED;
    }

    public function isFailed()
    {
        return $this->status === InvoiceStatus::FAILED;
    }

    public function isPending()
    {
        return $this->status === InvoiceStatus::PENDING;
    }

    public function markAsIssued()
    {
        $this->update(['status' => InvoiceStatus::INVOICED, 'issued_at' => now()]);
    }

    public function markAsFailed()
    {
        $this->update(['status' => InvoiceStatus::FAILED]);
    }

    public function markAsVoided()
    {
        $this->update(['status' => InvoiceStatus::VOIDED, 'voided_at' => now()]);
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

    public function scopeInvoiced($query)
    {
        return $query->where('status', InvoiceStatus::INVOICED);
    }

    public function getStatusLabelAttribute()
    {
        return InvoiceStatus::label($this->status);
    }
}
