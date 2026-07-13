<?php

namespace App\Models\Tenant;

class IdempotencyRequest extends BaseTenantModel
{
    protected $fillable = [
        'scope', 'idempotency_key_hash', 'request_hash', 'status', 'response_code',
        'response_body', 'resource_type', 'resource_id', 'locked_at', 'completed_at', 'expires_at',
    ];

    protected $casts = [
        'locked_at' => 'datetime',
        'completed_at' => 'datetime',
        'expires_at' => 'datetime',
    ];
}
