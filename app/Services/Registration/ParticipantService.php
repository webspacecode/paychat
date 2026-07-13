<?php
namespace App\Services\Registration;

use App\Models\Tenant\Customer;
use App\Models\Tenant\Registration\ParticipantProfile;
use App\Models\User;
use App\Services\CustomerIdentityService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ParticipantService
{
    public function __construct(private CustomerIdentityService $customers,private ParticipantCodeService $codes){}
    public function paginate(array $f){$per=max(1,min(100,(int)($f['per_page']??20)));return ParticipantProfile::query()->select(['id','customer_id','participant_code','first_name','last_name','display_name','date_of_birth','participant_phone','guardian_name','guardian_phone','photo_path','status','archived_at','created_at'])->with('customer:id,name,phone,email')->when(!($f['include_archived']??false),fn($q)=>$q->whereNull('archived_at'))->when($f['status']??null,fn($q,$v)=>$q->where('status',$v))->when($f['customer_id']??null,fn($q,$v)=>$q->where('customer_id',$v))->when($f['search']??null,fn($q,$v)=>$q->where(function($x)use($v){$x->where('participant_code','like',"{$v}%")->orWhere('first_name','like',"{$v}%")->orWhere('last_name','like',"{$v}%")->orWhere('participant_phone','like',"{$v}%")->orWhere('guardian_name','like',"{$v}%")->orWhere('guardian_phone','like',"{$v}%")->orWhereHas('customer',fn($c)=>$c->where('name','like',"{$v}%")->orWhere('phone','like',"{$v}%"));}))->latest()->paginate($per);}
    public function matches(array $data){return $this->customers->findMatches($data)->map(function($c){return ['id'=>$c->id,'name'=>$c->name,'phone_masked'=>$this->mask($c->phone),'email'=>$c->email,'participants'=>ParticipantProfile::where('customer_id',$c->id)->whereNull('archived_at')->limit(5)->get(['id','participant_code','display_name'])];});}
    public function create(array $data,User $actor): ParticipantProfile{return DB::connection('tenant')->transaction(function()use($data,$actor){if($data['customer_mode']==='existing')$customer=Customer::findOrFail($data['customer_id']);else{$matches=$this->customers->findMatches($data['customer']??[]);if($matches->isNotEmpty()&&!($data['confirm_create_despite_matches']??false))throw ValidationException::withMessages(['customer'=>'Possible existing contacts found. Confirm creation or select an existing contact.']);$customer=$this->customers->create($data['customer']);}$p=$data['participant'];$p['display_name']=trim($p['first_name'].' '.($p['last_name']??''));$profile=ParticipantProfile::create([...$this->fields($p),'customer_id'=>$customer->id,'participant_code'=>'PENDING-'.bin2hex(random_bytes(8)),'created_by'=>$actor->id,'updated_by'=>$actor->id]);$this->codes->assign($profile);return $profile->fresh()->load('customer');});}
    public function update(ParticipantProfile $p,array $data,User $actor): ParticipantProfile{$merged=array_merge($p->toArray(),$data);$data['display_name']=trim($merged['first_name'].' '.($merged['last_name']??''));$p->update([...$this->fields($data),'updated_by'=>$actor->id]);return $p->fresh()->load('customer');}
    public function archive(ParticipantProfile $p,User $actor):ParticipantProfile{$p->update(['status'=>'archived','archived_at'=>$p->archived_at?:now(),'updated_by'=>$actor->id]);return $p->fresh();}
    private function fields(array $d):array{return collect($d)->only(['first_name','last_name','display_name','date_of_birth','gender','participant_phone','participant_email','guardian_name','guardian_phone','emergency_contact','school_or_college','occupation','notes','custom_data','status'])->all();}
    private function mask(?string $v):?string{if(!$v)return null;$d=preg_replace('/\D+/','',$v);return strlen($d)>4?str_repeat('*',strlen($d)-4).substr($d,-4):$d;}
}
