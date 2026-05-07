<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DemoLead extends Model
{
    protected $fillable = [

        'name',
        'email',
        'phone',
        'business_name',
        'business_type',
        'counters',
        'preferred_demo_time',
        'source',
        'status',
        'notes',

    ];
}