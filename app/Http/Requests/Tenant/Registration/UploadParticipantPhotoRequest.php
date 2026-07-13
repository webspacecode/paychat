<?php
namespace App\Http\Requests\Tenant\Registration;
use Illuminate\Foundation\Http\FormRequest;
class UploadParticipantPhotoRequest extends FormRequest{public function authorize():bool{return true;}public function rules():array{return ['photo'=>'required|image|mimes:jpg,jpeg,png,webp|max:4096'];}}
