<?php

namespace App\Models\Tenant;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class PaymentMethodCorrection extends Model
{
    protected $connection = 'tenant';

    protected $fillable = [
        'order_id',
        'payment_id',
        'old_payment_method',
        'new_payment_method',
        'old_upi_profile_id',
        'new_upi_profile_id',
        'amount',
        'reason',
        'corrected_by',
        'corrected_at',
        'idempotency_key_hash',
        'meta',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'corrected_at' => 'datetime',
        'meta' => 'array',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }

    public function correctedBy()
    {
        return $this->belongsTo(User::class, 'corrected_by');
    }
}
