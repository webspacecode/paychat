<?php

namespace App\Models\Tenant;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $table = 'pos_payments';

    protected $fillable = [
        'order_id',
        'payment_method',
        'mode',
        'provider',
        'amount',
        'transaction_id',
        'provider_ref',
        'upi_profile_id',
        'upi_qr_url',
        'status',
        'collected_by',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array'
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function upiProfile()
    {
        return $this->belongsTo(UpiProfile::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function collectedBy()
    {
        return $this->belongsTo(User::class, 'collected_by');
    }
}
