<?php
namespace App\Services\Registration;

use App\Models\Tenant\Location;
use App\Models\Tenant\Registration\Program;
use App\Models\Tenant\Registration\ProgramBatch;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ProgramBatchService
{
    public const DAYS=['monday','tuesday','wednesday','thursday','friday','saturday','sunday'];
    public function paginate(Program $program,array $f){return $program->batches()->select(['id','program_id','name','start_date','end_date','start_time','end_time','days_of_week','capacity','location_id','status','archived_at'])->with('location:id,name')->when(!($f['include_archived']??false),fn($q)=>$q->whereNull('archived_at'))->when($f['status']??null,fn($q,$v)=>$q->where('status',$v))->latest()->paginate(max(1,min(100,(int)($f['per_page']??20))));}
    public function create(Program $program,array $data,User $actor): ProgramBatch {return DB::connection('tenant')->transaction(function()use($program,$data,$actor){$data=$this->validate($data);$batch=$program->batches()->create([...$this->fields($data),'created_by'=>$actor->id,'updated_by'=>$actor->id]);$this->syncInstructors($batch,$data['instructor_user_ids']??[]);return $batch->load(['location','instructors']);});}
    public function update(ProgramBatch $batch,array $data,User $actor): ProgramBatch{return DB::connection('tenant')->transaction(function()use($batch,$data,$actor){$data=$this->validate(array_merge($batch->toArray(),$data));$batch->update([...$this->fields($data),'updated_by'=>$actor->id]);if(array_key_exists('instructor_user_ids',$data))$this->syncInstructors($batch,$data['instructor_user_ids']);return $batch->fresh()->load(['program.product','location','instructors']);});}
    public function archive(ProgramBatch $batch,User $actor): ProgramBatch{$batch->update(['status'=>'archived','archived_at'=>$batch->archived_at?:now(),'updated_by'=>$actor->id]);return $batch->fresh();}
    private function validate(array $d):array{if(!empty($d['end_date'])&&!empty($d['start_date'])&&$d['end_date']<$d['start_date'])throw ValidationException::withMessages(['end_date'=>'End date must be on or after start date.']);if(!empty($d['start_time'])&&!empty($d['end_time'])&&$d['end_time']<=$d['start_time'])throw ValidationException::withMessages(['end_time'=>'End time must be later than start time.']);foreach($d['days_of_week']??[] as $day)if(!in_array($day,self::DAYS,true))throw ValidationException::withMessages(['days_of_week'=>'Invalid day value.']);if(!empty($d['location_id']))Location::findOrFail($d['location_id']);return $d;}
    private function syncInstructors(ProgramBatch $batch,array $ids):void{$raw=array_map('intval',$ids);$ids=array_values(array_unique($raw));if(count($ids)!==count($raw))throw ValidationException::withMessages(['instructor_user_ids'=>'Duplicate instructors are not allowed.']);$users=User::on('mysql')->where('tenant_id',app('currentTenant')->id)->whereIn('id',$ids)->pluck('id')->map(fn($v)=>(int)$v)->all();if(count($users)!==count($ids))throw ValidationException::withMessages(['instructor_user_ids'=>'One or more instructors are invalid.']);$batch->instructors()->delete();foreach($ids as $id)$batch->instructors()->create(['user_id'=>$id]);}
    private function fields(array $d):array{return collect($d)->only(['name','description','start_date','end_date','start_time','end_time','days_of_week','capacity','location_id','status','settings'])->all();}
}
