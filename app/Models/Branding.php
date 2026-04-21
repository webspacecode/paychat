<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Branding extends Model
{
    protected $fillable = [
        'tenant_id','company_name','logo','primary_color','phone','address'
    ];
}