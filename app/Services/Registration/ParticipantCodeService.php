<?php
namespace App\Services\Registration;

use App\Models\Tenant\Registration\ParticipantProfile;

class ParticipantCodeService
{
    public function assign(ParticipantProfile $participant): string
    {
        $code='PT-'.str_pad((string)$participant->id,6,'0',STR_PAD_LEFT);
        $participant->forceFill(['participant_code'=>$code])->save();
        return $code;
    }
}
