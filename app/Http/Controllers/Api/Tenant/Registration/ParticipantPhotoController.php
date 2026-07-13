<?php
namespace App\Http\Controllers\Api\Tenant\Registration;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\Registration\UploadParticipantPhotoRequest;
use App\Models\Tenant\Registration\ParticipantProfile;
use App\Services\Registration\ParticipantPhotoService;
use Illuminate\Support\Facades\Storage;
class ParticipantPhotoController extends Controller{public function store(string $tenantSlug,ParticipantProfile $participant,UploadParticipantPhotoRequest $r,ParticipantPhotoService $s){return response()->json($s->replace($participant,$r->file('photo')));}public function show(string $tenantSlug,ParticipantProfile $participant){abort_unless($participant->photo_path&&Storage::disk('local')->exists($participant->photo_path),404);return Storage::disk('local')->response($participant->photo_path);}public function destroy(string $tenantSlug,ParticipantProfile $participant,ParticipantPhotoService $s){$s->remove($participant);return response()->json(['message'=>'Photo removed.']);}}
