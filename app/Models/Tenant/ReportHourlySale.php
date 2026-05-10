<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

class ReportHourlySale extends Model
{
    protected $table = 'report_hourly_sales';

    protected $fillable = [
        'tenant_id',
        'location_id',
        'date',
        'hour',
        'orders_count',
        'revenue',
    ];

    protected $casts = [
        'date' => 'date',
        'hour' => 'integer',
        'orders_count' => 'integer',
        'revenue' => 'decimal:2',
    ];

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeForTenant($query, $tenantId)
    {
        return $query->where('tenant_id', $tenantId);
    }

    public function scopeForDate($query, $date)
    {
        return $query->whereDate('date', $date);
    }

    public function scopeForRange($query, $start, $end)
    {
        return $query->whereBetween('date', [$start, $end]);
    }

    public function scopeForHour($query, $hour)
    {
        return $query->where('hour', $hour);
    }

    public function scopeForToday($query)
    {
        return $query->whereDate('date', now());
    }

    public function scopeOrderByHour($query)
    {
        return $query->orderBy('hour');
    }
}