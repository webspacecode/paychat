<?php

namespace App\Exceptions;

use RuntimeException;

class PaymentException extends RuntimeException
{
    public function __construct(
        string $message,
        private readonly string $errorCode,
        private readonly int $status = 422,
        private readonly array $context = []
    ) {
        parent::__construct($message);
    }

    public function render($request)
    {
        return response()->json(array_filter([
            'message' => $this->getMessage(),
            'code' => $this->errorCode,
            'support_code' => $request->attributes->get('request_id'),
            'details' => $this->context,
        ], fn ($value) => $value !== null && $value !== []), $this->status);
    }

    public function getStatusCode(): int
    {
        return $this->status;
    }
}
