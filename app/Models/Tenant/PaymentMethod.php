<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
    protected $fillable = ['type','mode','enabled','config'];

    protected $casts = [
        'config' => 'array'
    ];
}
