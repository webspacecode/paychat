<?php

namespace App\Exceptions;

use Symfony\Component\HttpKernel\Exception\HttpException;

class IdempotencyException extends HttpException
{
    public function __construct(string $message, public readonly string $errorCode, public readonly int $status = 409)
    {
        parent::__construct($status, $message);
    }
}
