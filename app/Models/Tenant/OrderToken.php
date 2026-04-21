<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

class OrderToken extends Model
{
    protected $fillable = [
        'order_id',
        'token_number',
        'token_code',
        'token_date',
        'status'
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}