<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

class ReportDailySale extends Model
{
    protected $fillable = [
        'tenant_id','location_id','date',
        'total_orders','total_sales','total_tax',
        'total_discount','net_sales','avg_order_value'
    ];
}
