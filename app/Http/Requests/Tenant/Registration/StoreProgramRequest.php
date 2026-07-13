<?php
namespace App\Http\Requests\Tenant\Registration;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Services\Registration\ProgramDurationService;
class StoreProgramRequest extends FormRequest
{
 public function authorize():bool{return true;}
 public function rules():array{return [
  'product_mode'=>['required',Rule::in(['new','existing'])], 'product_id'=>'required_if:product_mode,existing|nullable|integer',
  'product'=>'required_if:product_mode,new|nullable|array', 'product.name'=>'required_if:product_mode,new|string|max:255',
  'product.sku'=>'required_if:product_mode,new|nullable|string|max:255|unique:products,sku', 'product.price'=>'required_if:product_mode,new|numeric|min:0',
  'program_type'=>'nullable|string|max:50','description'=>'nullable|string|max:5000','duration_type'=>['required',Rule::in(ProgramDurationService::TYPES)],
  'duration_value'=>'nullable|integer|min:1','start_date'=>'nullable|date','end_date'=>'nullable|date','registration_open_date'=>'nullable|date',
  'registration_close_date'=>'nullable|date|after_or_equal:registration_open_date','capacity'=>'nullable|integer|min:1','renewable'=>'nullable|boolean',
  'renewal_frequency'=>'nullable|string|max:40','status'=>['nullable',Rule::in(['draft','active','inactive'])],'settings'=>'nullable|array'];
 }
}
