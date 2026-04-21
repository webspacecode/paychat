<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $table = 'pos_payments';

    protected $fillable = ['order_id','payment_method','amount','transaction_id','upi_qr_url','status'];

    protected $casts = [
        'meta' => 'array'
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }
}
