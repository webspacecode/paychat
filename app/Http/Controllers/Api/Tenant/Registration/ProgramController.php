<?php
namespace App\Http\Controllers\Api\Tenant\Registration;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\Registration\StoreProgramRequest;
use App\Http\Requests\Tenant\Registration\UpdateProgramRequest;
use App\Models\Tenant\Registration\Program;
use App\Models\Tenant\Product;
use App\Services\Registration\ProgramService;
use App\Services\Registration\RegistrationIdempotencyService;
use App\Support\Observability;
use Illuminate\Http\Request;
class ProgramController extends Controller{
 public function index(Request $r,ProgramService $s){return response()->json($s->paginate($r->validate(['search'=>'nullable|string|max:150','status'=>'nullable|string|max:30','program_type'=>'nullable|string|max:50','duration_type'=>'nullable|string|max:40','include_archived'=>'nullable|boolean','per_page'=>'nullable|integer|min:1|max:100','sort'=>'nullable|string','direction'=>'nullable|in:asc,desc'])));}
 public function compatibleProducts(Request $r){$v=$r->validate(['search'=>'nullable|string|max:150','per_page'=>'nullable|integer|min:1|max:50']);return response()->json(Product::query()->select(['id','name','sku','price'])->where('type','basic')->where('track_inventory',false)->where('is_active',true)->whereDoesntHave('program')->when($v['search']??null,fn($q,$x)=>$q->where(fn($w)=>$w->where('name','like',"{$x}%")->orWhere('sku','like',"{$x}%")))->orderBy('name')->paginate($v['per_page']??20));}
 public function store(StoreProgramRequest $r,ProgramService $s,RegistrationIdempotencyService $i){$key=(string)$r->header('X-Idempotency-Key');if($key==='')abort(422,'X-Idempotency-Key is required.');$result=$i->run('registration.program.create',$key,$r->validated(),fn()=>$s->create($r->validated(),$r->user()));Observability::logInfo('registration.program.created',['resource_id'=>$result['body']['id']??null],$r);return response()->json($result['body'],$result['status']);}
 public function show(string $tenantSlug,Program $program){return response()->json($program->load(['product','batches'=>fn($q)=>$q->whereNull('archived_at')->with(['location','instructors'])->withCount(['registrations'=>fn($x)=>$x->whereIn('status',['active','on_hold'])])->latest()->limit(20),'registrations'=>fn($q)=>$q->with(['participant','batch'])->latest('registered_on')->limit(10)])->loadCount(['registrations'=>fn($q)=>$q->whereIn('status',['active','on_hold'])]));}
 public function update(string $tenantSlug,Program $program,UpdateProgramRequest $r,ProgramService $s){$p=$s->update($program,$r->validated(),$r->user());Observability::logInfo('registration.program.updated',['resource_id'=>$p->id],$r);return response()->json($p);}
 public function archive(string $tenantSlug,Program $program,Request $r,ProgramService $s){$v=$r->validate(['disable_linked_product'=>'nullable|boolean']);$p=$s->archive($program,$r->user(),(bool)($v['disable_linked_product']??false));Observability::logInfo('registration.program.archived',['resource_id'=>$p->id,'linked_product_disabled'=>(bool)($v['disable_linked_product']??false)],$r);return response()->json($p);}
}
