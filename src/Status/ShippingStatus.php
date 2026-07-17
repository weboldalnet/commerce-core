<?php

namespace Weboldalnet\CommerceCore\Status;

class ShippingStatus
{
    const NOT_REQUIRED = 'not_required';
    const PENDING = 'pending';
    const PREPARED = 'prepared';
    const SHIPPED = 'shipped';
    const DELIVERED = 'delivered';
    const FAILED = 'failed';

    const ALL = [
        self::NOT_REQUIRED,
        self::PENDING,
        self::PREPARED,
        self::SHIPPED,
        self::DELIVERED,
        self::FAILED,
    ];

    const LABELS = [
        self::NOT_REQUIRED => 'Nem szükséges',
        self::PENDING => 'Függőben',
        self::PREPARED => 'Előkészítve',
        self::SHIPPED => 'Kiszállítva',
        self::DELIVERED => 'Kézbesítve',
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
