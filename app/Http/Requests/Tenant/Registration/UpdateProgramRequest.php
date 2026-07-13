<?php
namespace App\Http\Requests\Tenant\Registration;
use Illuminate\Validation\Rule;
class UpdateProgramRequest extends StoreProgramRequest
{
 public function rules():array{$r=parent::rules();unset($r['product_mode'],$r['product_id']);$r['product']='nullable|array';$r['product.name']='sometimes|string|max:255';$r['product.sku']=['sometimes','nullable','string','max:255',Rule::unique('products','sku')->ignore($this->route('program')?->product_id)];$r['product.price']='sometimes|numeric|min:0';$r['duration_type'][0]='sometimes';return $r;}
}
