<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReviewSession extends Model
{
    protected $connection = 'mysql';

    protected $fillable = [

        'tenant_id',
        'tenant_slug',
        'tenant_api_key',

        'invoice_number',
        'order_id',

        'customer_name',
        'customer_phone',

        'review_token',

        'is_reviewed',
        'reviewed_at',

        'expires_at',
    ];
}
