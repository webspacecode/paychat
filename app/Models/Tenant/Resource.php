<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

class Resource extends Model
{
    protected $fillable = [
        'location_id',
        'name',
        'code',
        'type',
        'capacity',
        'status',
        'meta'
    ];

    protected $casts = [
        'meta' => 'array'
    ];

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function activeOrder()
    {
        return $this->hasOne(Order::class)
            ->whereIn('status', ['draft', 'pending_payment']);
    }
}