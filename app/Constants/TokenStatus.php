<?php

namespace App\Constants;

class TokenStatus
{
    const WAITING = 'waiting';
    const PENDING = 'pending';
    const PREPARING = 'preparing';
    const READY = 'ready';

    public static function all()
    {
        return [
            self::WAITING,
            self::PENDING,
            self::PREPARING,
            self::READY
        ];
    }
}