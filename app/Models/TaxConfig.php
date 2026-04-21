<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaxConfig extends Model
{
    protected $fillable = [
        'tenant_id','gst_number','is_gst_enabled','is_inclusive'
    ];
}