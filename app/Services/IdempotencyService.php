<?php

namespace App\Services;

use App\Exceptions\IdempotencyException;
use App\Models\Tenant\IdempotencyRequest;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

class IdempotencyService
{
    public const MAX_KEY_LENGTH = 200;
    public const MAX_RESPONSE_BYTES = 65535;

    public function acquire(string $scope, string $key, array $payload): array
    {
        $this->validate($scope, $key);
        $keyHash = hash('sha256', $key);
        $requestHash = hash('sha256', json_encode($this->canonicalize($payload), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));

        try {
            $record = IdempotencyRequest::create([
                'scope' => $scope,
                'idempotency_key_hash' => $keyHash,
                'request_hash' => $requestHash,
                'status' => 'processing',
                'locked_at' => now(),
                'expires_at' => now()->addHours($this->retentionHours()),
            ]);
            return ['execute' => true, 'record' => $record];
        } catch (QueryException $e) {
            if (! $this->isUniqueViolation($e)) throw $e;
        }

        return DB::connection('tenant')->transaction(function () use ($scope, $keyHash, $requestHash) {
            $record = IdempotencyRequest::where('scope', $scope)
                ->where('idempotency_key_hash', $keyHash)->lockForUpdate()->firstOrFail();
            if (! hash_equals($record->request_hash, $requestHash)) {
                throw new IdempotencyException('Idempotency key was already used with a different request.', 'IDEMPOTENCY_CONFLICT');
            }
            if ($record->status === 'completed') {
                return ['execute' => false, 'replay' => true, 'record' => $record, 'response' => json_decode($record->response_body ?: 'null', true)];
            }
            if ($record->status === 'processing' && $record->locked_at?->gt(now()->subMinutes(5))) {
                throw new IdempotencyException('An identical request is already in progress.', 'IDEMPOTENCY_IN_PROGRESS');
            }
            $record->update(['status' => 'processing', 'locked_at' => now(), 'expires_at' => now()->addHours($this->retentionHours())]);
            return ['execute' => true, 'record' => $record->fresh()];
        }, 3);
    }

    public function complete(IdempotencyRequest $record, int $responseCode, mixed $response, ?string $resourceType = null, ?int $resourceId = null): void
    {
        $encoded = json_encode($response, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        if ($encoded === false || strlen($encoded) > self::MAX_RESPONSE_BYTES) {
            throw new IdempotencyException('Idempotent response is too large to store.', 'IDEMPOTENCY_RESPONSE_TOO_LARGE', 500);
        }
        $record->update([
            'status' => 'completed', 'response_code' => $responseCode, 'response_body' => $encoded,
            'resource_type' => $resourceType, 'resource_id' => $resourceId,
            'completed_at' => now(), 'locked_at' => null,
        ]);
    }

    public function fail(IdempotencyRequest $record): void
    {
        $record->update(['status' => 'failed', 'locked_at' => null, 'response_body' => null]);
    }

    public function pruneExpired(): int
    {
        return IdempotencyRequest::where('expires_at', '<', now())->delete();
    }

    private function validate(string $scope, string $key): void
    {
        if (! preg_match('/^[a-z0-9._-]{1,100}$/', $scope)) throw new IdempotencyException('Invalid idempotency scope.', 'IDEMPOTENCY_SCOPE_INVALID', 422);
        if ($key === '' || strlen($key) > self::MAX_KEY_LENGTH || ! preg_match('/^[A-Za-z0-9._:-]+$/', $key)) {
            throw new IdempotencyException('Invalid idempotency key.', 'IDEMPOTENCY_KEY_INVALID', 422);
        }
    }

    private function canonicalize(array $payload): array
    {
        ksort($payload);
        foreach ($payload as &$value) if (is_array($value)) $value = $this->canonicalize($value);
        return $payload;
    }

    private function retentionHours(): int
    {
        return max(24, min(72, (int) config('modules.idempotency_retention_hours', 48)));
    }

    private function isUniqueViolation(QueryException $e): bool
    {
        return in_array((string) ($e->errorInfo[0] ?? $e->getCode()), ['23000', '23505'], true)
            || str_contains(strtolower($e->getMessage()), 'unique');
    }
}
