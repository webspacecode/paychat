<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    protected $connection = 'mysql';

    protected $fillable = [

        'review_session_id',

        'tenant_id',

        'tenant_slug',

        'rating',

        'review_text',

        'is_verified_purchase',

        'is_approved',

        'ip_address',

        'user_agent',
    ];
}
