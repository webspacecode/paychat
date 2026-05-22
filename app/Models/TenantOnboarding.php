<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TenantOnboarding extends Model
{
    protected $fillable = [
        'tenant_id',
        'status',
        'failed_reason',
        'setup_started_at',
        'setup_completed_at',
    ];

    protected $casts = [
        'setup_started_at' => 'datetime',
        'setup_completed_at' => 'datetime',
    ];
}
