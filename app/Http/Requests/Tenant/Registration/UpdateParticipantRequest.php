<?php
namespace App\Http\Requests\Tenant\Registration;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
class UpdateParticipantRequest extends FormRequest{public function authorize():bool{return true;}public function rules():array{return ['first_name'=>'required|string|max:100','last_name'=>'nullable|string|max:100','date_of_birth'=>'nullable|date|before_or_equal:today','gender'=>'nullable|string|max:30','participant_phone'=>'nullable|string|max:50','participant_email'=>'nullable|email|max:150','guardian_name'=>'nullable|string|max:150','guardian_phone'=>'nullable|string|max:50','emergency_contact'=>'nullable|string|max:100','school_or_college'=>'nullable|string|max:200','occupation'=>'nullable|string|max:150','notes'=>'nullable|string|max:5000','custom_data'=>'nullable|array','status'=>['nullable',Rule::in(['active','inactive'])]];}}
