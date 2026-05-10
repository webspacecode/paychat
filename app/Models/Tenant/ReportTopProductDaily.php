<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

class ReportTopProductDaily extends Model
{
    protected $table = 'report_top_products_daily';

    protected $fillable = [
        'tenant_id',
        'location_id',
        'date',
        'product_id',
        'product_name',
        'quantity_sold',
        'revenue',
        'rank',
    ];

    protected $casts = [
        'date' => 'date',
        'quantity_sold' => 'integer',
        'revenue' => 'decimal:2',
        'rank' => 'integer',
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

    public function scopeTop($query, $limit = 10)
    {
        return $query->orderBy('rank')->limit($limit);
    }

    public function scopeByProduct($query, $productId)
    {
        return $query->where('product_id', $productId);
    }
}