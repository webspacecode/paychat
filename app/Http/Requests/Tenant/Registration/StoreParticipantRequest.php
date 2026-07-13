<?php
namespace App\Http\Requests\Tenant\Registration;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
class StoreParticipantRequest extends FormRequest
{
 public function authorize():bool{return true;}
 public function rules():array{return [
  'customer_mode'=>['required',Rule::in(['existing','new'])],'customer_id'=>'required_if:customer_mode,existing|nullable|integer|exists:pos_customers,id',
  'customer'=>'required_if:customer_mode,new|nullable|array','customer.name'=>'required_if:customer_mode,new|string|max:150','customer.phone'=>'nullable|string|max:50',
  'customer.email'=>'nullable|email|max:150','customer.address'=>'nullable|string|max:1000','confirm_create_despite_matches'=>'nullable|boolean','participant'=>'required|array','participant.first_name'=>'required|string|max:100',
  'participant.last_name'=>'nullable|string|max:100','participant.date_of_birth'=>'nullable|date|before_or_equal:today','participant.gender'=>'nullable|string|max:30',
  'participant.participant_phone'=>'nullable|string|max:50','participant.participant_email'=>'nullable|email|max:150','participant.guardian_name'=>'nullable|string|max:150',
  'participant.guardian_phone'=>'nullable|string|max:50','participant.emergency_contact'=>'nullable|string|max:100','participant.school_or_college'=>'nullable|string|max:200',
  'participant.occupation'=>'nullable|string|max:150','participant.notes'=>'nullable|string|max:5000','participant.custom_data'=>'nullable|array',
  'participant.status'=>['nullable',Rule::in(['active','inactive'])]];
 }
}
