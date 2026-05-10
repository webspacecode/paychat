<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

class ReportKpiSummary extends Model
{
    protected $table = 'report_kpi_summaries';

    protected $fillable = [
        'tenant_id',
        'location_id',
        'date',
        'sales',
        'orders',
        'avg_order',
        'growth_percent',
        'peak_hour',
        'top_product_id',
        'upi_percent',
        'cash_percent',
        'card_percent',
    ];

    protected $casts = [
        'date' => 'date',

        'sales' => 'decimal:2',
        'avg_order' => 'decimal:2',
        'growth_percent' => 'decimal:2',

        'upi_percent' => 'decimal:2',
        'cash_percent' => 'decimal:2',
        'card_percent' => 'decimal:2',

        'orders' => 'integer',
        'peak_hour' => 'integer',
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

    public function scopeForLocation($query, $locationId)
    {
        return $query->where('location_id', $locationId);
    }

    public function scopeForDate($query, $date)
    {
        return $query->whereDate('date', $date);
    }

    public function scopeForRange($query, $start, $end)
    {
        return $query->whereBetween('date', [$start, $end]);
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function topProduct()
    {
        return $this->belongsTo(Product::class, 'top_product_id');
    }
}