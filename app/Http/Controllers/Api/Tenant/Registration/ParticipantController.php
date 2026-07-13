<?php
namespace App\Http\Controllers\Api\Tenant\Registration;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\Registration\StoreParticipantRequest;
use App\Http\Requests\Tenant\Registration\UpdateParticipantRequest;
use App\Models\Tenant\Registration\ParticipantProfile;
use App\Services\Registration\ParticipantService;
use App\Services\Registration\RegistrationIdempotencyService;
use App\Support\Observability;
use Illuminate\Http\Request;
class ParticipantController extends Controller{
 public function index(Request $r,ParticipantService $s){return response()->json($s->paginate($r->validate(['search'=>'nullable|string|max:150','status'=>'nullable|string|max:30','customer_id'=>'nullable|integer','include_archived'=>'nullable|boolean','per_page'=>'nullable|integer|min:1|max:100'])));}
 public function matches(Request $r,ParticipantService $s){return response()->json(['data'=>$s->matches($r->validate(['name'=>'nullable|string|max:150','phone'=>'nullable|string|max:50','email'=>'nullable|email|max:150']))]);}
 public function store(StoreParticipantRequest $r,ParticipantService $s,RegistrationIdempotencyService $i){$key=(string)$r->header('X-Idempotency-Key');if($key==='')abort(422,'X-Idempotency-Key is required.');$result=$i->run('registration.participant.create',$key,$r->validated(),fn()=>$s->create($r->validated(),$r->user()));Observability::logInfo('registration.participant.created',['resource_id'=>$result['body']['id']??null],$r);return response()->json($result['body'],$result['status']);}
 public function show(string $tenantSlug,ParticipantProfile $participant){return response()->json($participant->load(['customer','registrations'=>fn($q)=>$q->with(['program.product','batch'])->latest('registered_on')])->setAttribute('siblings',ParticipantProfile::where('customer_id',$participant->customer_id)->whereKeyNot($participant->id)->whereNull('archived_at')->limit(10)->get(['id','participant_code','display_name','status'])));}
 public function update(string $tenantSlug,ParticipantProfile $participant,UpdateParticipantRequest $r,ParticipantService $s){$p=$s->update($participant,$r->validated(),$r->user());Observability::logInfo('registration.participant.updated',['resource_id'=>$p->id],$r);return response()->json($p);}
 public function archive(string $tenantSlug,ParticipantProfile $participant,Request $r,ParticipantService $s){$p=$s->archive($participant,$r->user());Observability::logInfo('registration.participant.archived',['resource_id'=>$p->id],$r);return response()->json($p);}
}
