<?php

namespace Weboldalnet\CommerceCore\Status;

class OrderStatus
{
    const PENDING = 'pending';
    const PROCESSING = 'processing';
    const COMPLETED = 'completed';
    const CANCELLED = 'cancelled';
    const FAILED = 'failed';

    const ALL = [
        self::PENDING,
        self::PROCESSING,
        self::COMPLETED,
        self::CANCELLED,
        self::FAILED,
    ];

    const LABELS = [
        self::PENDING => 'Függőben',
        self::PROCESSING => 'Feldolgozás alatt',
        self::COMPLETED => 'Teljesítve',
        self::CANCELLED => 'Törölve',
        self::FAILED => 'Sikertelen',
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
