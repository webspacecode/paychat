<?php

namespace App\Models\Tenant\Registration;

use App\Models\Tenant\BaseTenantModel;
use App\Models\Tenant\Order;

class ProgramRegistration extends BaseTenantModel
{
    protected $guarded = ['id'];

    protected $casts = [
        'registered_on' => 'date',
        'starts_on' => 'date',
        'ends_on' => 'date',
        'fee_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'final_amount' => 'decimal:2',
        'cancelled_at' => 'datetime',
    ];

    public function participant()
    {
        return $this->belongsTo(ParticipantProfile::class, 'participant_profile_id');
    }

    public function program()
    {
        return $this->belongsTo(Program::class);
    }

    public function batch()
    {
        return $this->belongsTo(ProgramBatch::class, 'program_batch_id');
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
