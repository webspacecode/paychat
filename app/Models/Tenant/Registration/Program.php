<?php
namespace App\Models\Tenant\Registration;

use App\Models\Tenant\BaseTenantModel;
use App\Models\Tenant\Product;

class Program extends BaseTenantModel
{
    protected $guarded = ['id'];
    protected $casts = ['start_date'=>'date','end_date'=>'date','registration_open_date'=>'date','registration_close_date'=>'date','renewable'=>'boolean','settings'=>'array','archived_at'=>'datetime'];
    public function product() { return $this->belongsTo(Product::class); }
    public function batches() { return $this->hasMany(ProgramBatch::class); }
    public function registrations() { return $this->hasMany(ProgramRegistration::class); }
}
