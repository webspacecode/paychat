<?php
namespace App\Services\Registration;

use App\Models\Tenant\Registration\ParticipantProfile;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ParticipantPhotoService
{
    public function replace(ParticipantProfile $participant,UploadedFile $file):ParticipantProfile{$tenant=app('currentTenant');$path=$file->storeAs("tenants/{$tenant->id}/registration/participants/{$participant->id}",Str::uuid().'.'.$file->extension(),'local');$old=$participant->photo_path;$participant->update(['photo_path'=>$path,'updated_by'=>auth()->id()]);if($old&&$old!==$path)Storage::disk('local')->delete($old);return $participant->fresh();}
    public function remove(ParticipantProfile $participant):void{if($participant->photo_path)Storage::disk('local')->delete($participant->photo_path);$participant->update(['photo_path'=>null,'updated_by'=>auth()->id()]);}
}
