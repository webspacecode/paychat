<?php
namespace App\Http\Requests\Tenant\Registration;
class UpdateProgramBatchRequest extends StoreProgramBatchRequest{public function rules():array{$r=parent::rules();$r['name']='sometimes|string|max:150';return $r;}}
