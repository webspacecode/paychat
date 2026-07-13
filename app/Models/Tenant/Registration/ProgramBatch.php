<?php
namespace App\Models\Tenant\Registration;

use App\Models\Tenant\BaseTenantModel;
use App\Models\Tenant\Location;

class ProgramBatch extends BaseTenantModel
{
    protected $guarded = ['id'];
    protected $casts = ['start_date'=>'date','end_date'=>'date','days_of_week'=>'array','settings'=>'array','archived_at'=>'datetime'];
    public function program() { return $this->belongsTo(Program::class); }
    public function location() { return $this->belongsTo(Location::class); }
    public function instructors() { return $this->hasMany(ProgramBatchInstructor::class); }
    public function registrations() { return $this->hasMany(ProgramRegistration::class); }
}
