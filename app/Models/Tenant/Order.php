<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $table = 'pos_orders';

    protected $fillable = [

        // Identification
        'order_no',
        'invoice_no',

        // Location & Terminal
        'location_id',
        'terminal_id',

        // User
        'created_by',

        // Customer
        'customer_id',
        'customer_name',
        'customer_email',
        'customer_phone',

        // Order Classification
        'order_type',      // dine_in, takeaway, delivery, walk_in
        'source',          // pos, web, app
        'notes',

        // Status Flow
        'status',          // draft, pending_payment, paid, cancelled, refunded
        'payment_status',  // unpaid, partial, paid, refunded

        // Monetary Values
        'subtotal',
        'discount',
        'discount_type',   // fixed, percentage
        'tax',
        'tax_rate',
        'service_charge',
        'rounding',
        'total',

        // Payment Tracking
        'paid_amount',
        'change_amount',

        // Timestamps (Business)
        'paid_at',
        'completed_at',
        'cancelled_at',
    ];

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function resource()
    {
        return $this->belongsTo(Resource::class);
    }

    public function token()
    {
        return $this->hasOne(OrderToken::class, 'order_id');
    }
}
