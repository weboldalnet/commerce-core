<?php

namespace Weboldalnet\CommerceCore\Status;

class PaymentStatus
{
    const UNPAID = 'unpaid';
    const PENDING = 'pending';
    const PAID = 'paid';
    const FAILED = 'failed';
    const CANCELLED = 'cancelled';
    const REFUNDED = 'refunded';

    const ALL = [
        self::UNPAID,
        self::PENDING,
        self::PAID,
        self::FAILED,
        self::CANCELLED,
        self::REFUNDED,
    ];

    const LABELS = [
        self::UNPAID => 'Fizetetlen',
        self::PENDING => 'Függőben',
        self::PAID => 'Fizetve',
        self::FAILED => 'Sikertelen',
        self::CANCELLED => 'Törölve',
        self::REFUNDED => 'Visszatérítve',
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
