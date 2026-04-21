<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    protected $fillable = [
        'tenant_id','uuid','industry','paper_size','order_data'
    ];

    protected $casts = ['order_data'=>'array'];

    public function tenant() {
        return $this->belongsTo(Tenant::class);
    }
}