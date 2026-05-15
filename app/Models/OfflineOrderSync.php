<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OfflineOrderSync extends Model
{
    protected $fillable = [
        'tenant_id',
        'local_order_id',
        'backend_order_id',
        'status',
        'payload',
        'response',
        'error_message',
        'synced_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'response' => 'array',
        'synced_at' => 'datetime',
    ];
}
