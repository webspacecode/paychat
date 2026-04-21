<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tenant extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'email',
        'phone',
        'address',
        'logo',
        'gst_number',
        'pan_number',
        'registration_number',
        'plan',
        'is_active',
        'plan_expiry',
        'settings',
        'created_by',
        'database',
        'industry',
        'api_key'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'plan_expiry' => 'date',
        'settings' => 'array',
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function branding() {
        return $this->hasOne(Branding::class);
    }

    public function taxConfig() {
        return $this->hasOne(TaxConfig::class);
    }
}
