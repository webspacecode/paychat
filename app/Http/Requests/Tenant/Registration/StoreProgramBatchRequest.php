<?php
namespace App\Http\Requests\Tenant\Registration;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Services\Registration\ProgramBatchService;
class StoreProgramBatchRequest extends FormRequest{public function authorize():bool{return true;}public function rules():array{return ['name'=>'required|string|max:150','description'=>'nullable|string|max:5000','start_date'=>'nullable|date','end_date'=>'nullable|date|after_or_equal:start_date','start_time'=>'nullable|date_format:H:i','end_time'=>'nullable|date_format:H:i|after:start_time','days_of_week'=>'nullable|array','days_of_week.*'=>['string',Rule::in(ProgramBatchService::DAYS)],'capacity'=>'nullable|integer|min:1','location_id'=>'nullable|integer|exists:locations,id','status'=>['nullable',Rule::in(['draft','active','inactive'])],'settings'=>'nullable|array','instructor_user_ids'=>'nullable|array|max:20','instructor_user_ids.*'=>'integer|distinct'];}}
