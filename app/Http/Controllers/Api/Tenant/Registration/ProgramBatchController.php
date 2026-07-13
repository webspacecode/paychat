<?php
namespace App\Http\Controllers\Api\Tenant\Registration;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\Registration\StoreProgramBatchRequest;
use App\Http\Requests\Tenant\Registration\UpdateProgramBatchRequest;
use App\Models\Tenant\Registration\Program;
use App\Models\Tenant\Registration\ProgramBatch;
use App\Models\User;
use App\Services\Registration\ProgramBatchService;
use App\Services\Registration\RegistrationIdempotencyService;
use App\Support\Observability;
use Illuminate\Http\Request;
class ProgramBatchController extends Controller{
 public function index(string $tenantSlug,Program $program,Request $r,ProgramBatchService $s){return response()->json($s->paginate($program,$r->validate(['status'=>'nullable|string|max:30','include_archived'=>'nullable|boolean','per_page'=>'nullable|integer|min:1|max:100'])));}
 public function store(string $tenantSlug,Program $program,StoreProgramBatchRequest $r,ProgramBatchService $s,RegistrationIdempotencyService $i){$key=(string)$r->header('X-Idempotency-Key');if($key==='')abort(422,'X-Idempotency-Key is required.');$payload=[...$r->validated(),'program_id'=>$program->id];$result=$i->run('registration.batch.create',$key,$payload,fn()=>$s->create($program,$r->validated(),$r->user()));Observability::logInfo('registration.batch.created',['resource_id'=>$result['body']['id']??null],$r);return response()->json($result['body'],$result['status']);}
 public function show(string $tenantSlug,ProgramBatch $batch){$batch->load(['program.product','location','instructors']);$users=User::on('mysql')->whereIn('id',$batch->instructors->pluck('user_id'))->get(['id','name','role'])->keyBy('id');$batch->setAttribute('instructor_users',$batch->instructors->map(fn($x)=>$users->get($x->user_id))->filter()->values());return response()->json($batch);}
 public function update(string $tenantSlug,ProgramBatch $batch,UpdateProgramBatchRequest $r,ProgramBatchService $s){$b=$s->update($batch,$r->validated(),$r->user());Observability::logInfo('registration.batch.updated',['resource_id'=>$b->id],$r);return response()->json($b);}
 public function archive(string $tenantSlug,ProgramBatch $batch,Request $r,ProgramBatchService $s){$b=$s->archive($batch,$r->user());Observability::logInfo('registration.batch.archived',['resource_id'=>$b->id],$r);return response()->json($b);}
 public function instructors(Request $r){$v=$r->validate(['search'=>'nullable|string|max:150','per_page'=>'nullable|integer|min:1|max:50']);return response()->json(User::on('mysql')->select(['id','name','email','role'])->where('tenant_id',app('currentTenant')->id)->when($v['search']??null,fn($q,$x)=>$q->where(fn($w)=>$w->where('name','like',"{$x}%")->orWhere('email','like',"{$x}%")))->orderBy('name')->paginate($v['per_page']??20));}
}
