<?php
namespace App\Models\Tenant\Registration;

use App\Models\Tenant\BaseTenantModel;

class ProgramBatchInstructor extends BaseTenantModel
{
    protected $fillable = ['program_batch_id', 'user_id'];
    public function batch() { return $this->belongsTo(ProgramBatch::class, 'program_batch_id'); }
}
