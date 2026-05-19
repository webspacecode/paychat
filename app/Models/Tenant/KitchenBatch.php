<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

class KitchenBatch extends Model
{
    protected $connection = 'tenant';

    protected $fillable = [
        'location_id',
        'order_id',
        'table_session_id',
        'table_id',
        'batch_number',
        'batch_code',
        'business_date',
        'status',
        'sent_at',
    ];

    protected $casts = [
        'business_date' => 'date',
        'sent_at' => 'datetime',
    ];

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function tableSession()
    {
        return $this->belongsTo(TableSession::class);
    }

    public function table()
    {
        return $this->belongsTo(Resource::class, 'table_id');
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class, 'kitchen_batch_id');
    }
}
