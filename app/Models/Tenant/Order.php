<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $connection = 'tenant';

    protected $table = 'pos_orders';

    protected $fillable = [

        // Identification
        'order_no',
        'invoice_id',
        'invoice_no',

        // Location & Terminal
        'location_id',
        'terminal_id',
        'table_id',
        'table_session_id',
        'guest_count',

        // User
        'created_by',

        // Customer
        'customer_id',
        'customer_name',
        'customer_email',
        'customer_phone',

        // Order Classification
        'business_date',
        'order_type',      // dine_in, takeaway, delivery, walk_in
        'dining_flow',     // qsr, table_service
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
        'cancelled_by',
        'cancel_reason',
        'cancel_reason_type',

        // Extra Metadata
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
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

    public function table()
    {
        return $this->belongsTo(Resource::class, 'table_id');
    }

    public function resourceTable()
    {
        return $this->belongsTo(Resource::class, 'table_id');
    }

    public function tableSession()
    {
        return $this->belongsTo(TableSession::class);
    }

    public function kitchenBatches()
    {
        return $this->hasMany(KitchenBatch::class);
    }

    public function token()
    {
        return $this->hasOne(OrderToken::class, 'order_id');
    }
}
