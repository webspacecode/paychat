<?php
namespace App\Models\Tenant\Registration;

use App\Models\Tenant\BaseTenantModel;
use App\Models\Tenant\Customer;

class ParticipantProfile extends BaseTenantModel
{
    protected $guarded = ['id'];
    protected $casts = ['date_of_birth'=>'date','custom_data'=>'array','archived_at'=>'datetime'];
    public function customer() { return $this->belongsTo(Customer::class); }
    public function registrations() { return $this->hasMany(ProgramRegistration::class); }
}
