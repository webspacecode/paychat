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
        return $this->hasMany(Order::class, 'table_id');
    }

    public function activeOrder()
    {
        return $this->hasOne(Order::class, 'table_id')
            ->where('dining_flow', 'table_service')
            ->whereIn('status', ['draft', 'pending_payment']);
    }

    public function tableSessions()
    {
        return $this->hasMany(TableSession::class, 'table_id');
    }

    public function activeTableSession()
    {
        return $this->hasOne(TableSession::class, 'table_id')
            ->where('status', 'active');
    }
}
