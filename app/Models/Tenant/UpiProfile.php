<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

class UpiProfile extends Model
{
    protected $fillable = [
        'location_id',
        'label',
        'upi_id',
        'payee_name',
        'is_default',
        'is_active',
        'sort_order',
        'notes',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
}
