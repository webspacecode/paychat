<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $fillable = ['payment_id','transaction_ref','status'];

    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }
}
