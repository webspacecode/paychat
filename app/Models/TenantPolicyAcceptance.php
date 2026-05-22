<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TenantPolicyAcceptance extends Model
{
    protected $fillable = [
        'tenant_id',
        'user_id',
        'type',
        'version',
        'accepted_at',
        'ip_address',
        'user_agent',
        'source',
    ];

    protected $casts = [
        'accepted_at' => 'datetime',
    ];
}
