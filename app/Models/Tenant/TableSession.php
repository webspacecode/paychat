<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

class TableSession extends Model
{
    protected $connection = 'tenant';

    protected $fillable = [
        'location_id',
        'table_id',
        'order_id',
        'guest_count',
        'status',
        'opened_at',
        'closed_at',
        'notes',
    ];

    protected $casts = [
        'opened_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function table()
    {
        return $this->belongsTo(Resource::class, 'table_id');
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
