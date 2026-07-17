<?php

namespace Weboldalnet\CommerceCore\Status;

class InvoiceStatus
{
    const NOT_REQUIRED = 'not_required';
    const PENDING = 'pending';
    const INVOICED = 'invoiced';
    const FAILED = 'failed';
    const VOIDED = 'voided';

    const ALL = [
        self::NOT_REQUIRED,
        self::PENDING,
        self::INVOICED,
        self::FAILED,
        self::VOIDED,
    ];

    const LABELS = [
        self::NOT_REQUIRED => 'Nem szükséges',
        self::PENDING => 'Függőben',
        self::INVOICED => 'Számlázva',
        self::FAILED => 'Sikertelen',
        self::VOIDED => 'Érvénytelenítve',
    ];

    public static function label($status)
    {
        return self::LABELS[$status] ?? $status;
    }

    public static function isValid($status)
    {
        return in_array($status, self::ALL);
    }
}
