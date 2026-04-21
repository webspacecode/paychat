<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

class WebhookLog extends Model
{
    protected $fillable = ['provider','payload','status'];
}
