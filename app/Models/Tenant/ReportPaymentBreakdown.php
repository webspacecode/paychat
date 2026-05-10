<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

class ReportPaymentBreakdown extends Model
{
    protected $table = 'report_payment_breakdowns';

    protected $fillable = [
        'tenant_id',
        'location_id',
        'date',
        'payment_method',
        'total_amount',
        'transaction_count',
        'percentage',
    ];

    protected $casts = [
        'date' => 'date',
        'total_amount' => 'decimal:2',
        'transaction_count' => 'integer',
        'percentage' => 'decimal:2',
    ];

    /*
    |--------------------------------------------------------------------------
    | Scopes (very useful for reporting)
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

    public function scopePaymentMethod($query, $method)
    {
        return $query->where('payment_method', $method);
    }
}