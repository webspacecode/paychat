<?php

namespace App\Services;

use App\Models\Invoice;

class InvoiceAccessService
{
    private const MAX_PUBLIC_HOURS = 24;
    private const TOKEN_TTL_MINUTES = 30;

    public function requiresVerification(Invoice $invoice): bool
    {
        return $invoice->created_at !== null
            && $invoice->created_at->lte(now()->subHours(self::MAX_PUBLIC_HOURS));
    }

    public function registeredPhone(Invoice $invoice): ?string
    {
        foreach ([
            'customer.phone',
            'walk_in_customer.phone',
            'customer_phone',
            'billing_phone',
            'phone',
        ] as $path) {
            $phone = $this->normalizePhone(data_get($invoice->order_data, $path));

            if ($phone !== null) {
                return $phone;
            }
        }

        return null;
    }

    public function phoneMatches(Invoice $invoice, string $phone): bool
    {
        $registeredPhone = $this->registeredPhone($invoice);
        $givenPhone = $this->normalizePhone($phone);

        return $registeredPhone !== null
            && $givenPhone !== null
            && $registeredPhone === $givenPhone;
    }

    public function issueToken(Invoice $invoice): string
    {
        $expiresAt = now()->addMinutes(self::TOKEN_TTL_MINUTES)->timestamp;
        $payload = $invoice->uuid.'|'.$expiresAt;
        $signature = hash_hmac('sha256', $payload, config('app.key'));

        return rtrim(strtr(base64_encode($payload.'|'.$signature), '+/', '-_'), '=');
    }

    public function tokenAllows(Invoice $invoice, ?string $token): bool
    {
        if (! $this->requiresVerification($invoice)) {
            return true;
        }

        if (! $token) {
            return false;
        }

        $encoded = strtr($token, '-_', '+/');
        $encoded .= str_repeat('=', (4 - strlen($encoded) % 4) % 4);
        $decoded = base64_decode($encoded, true);

        if (! $decoded) {
            return false;
        }

        [$uuid, $expiresAt, $signature] = array_pad(explode('|', $decoded, 3), 3, null);

        if ($uuid !== $invoice->uuid || ! is_numeric($expiresAt) || (int) $expiresAt < now()->timestamp) {
            return false;
        }

        $payload = $uuid.'|'.$expiresAt;
        $expected = hash_hmac('sha256', $payload, config('app.key'));

        return is_string($signature) && hash_equals($expected, $signature);
    }

    private function normalizePhone($phone): ?string
    {
        $digits = preg_replace('/\D+/', '', (string) ($phone ?? ''));

        if ($digits === '') {
            return null;
        }

        if (strlen($digits) > 10 && str_starts_with($digits, '91')) {
            $digits = substr($digits, -10);
        }

        return $digits;
    }
}
