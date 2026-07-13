<?php
namespace App\Services\Registration;

use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

class ProgramDurationService
{
    public const TYPES = ['fixed_dates','single_day','days_from_registration','months_from_registration','years_from_registration','no_expiry'];

    public function normalizeDefinition(array $data): array
    {
        $type = $data['duration_type'] ?? null;
        if (! in_array($type, self::TYPES, true)) throw ValidationException::withMessages(['duration_type'=>'Invalid duration type.']);
        $valueTypes = ['days_from_registration','months_from_registration','years_from_registration'];
        if (in_array($type, $valueTypes, true)) {
            if (empty($data['duration_value']) || (int) $data['duration_value'] < 1) throw ValidationException::withMessages(['duration_value'=>'A positive duration value is required.']);
            if (! empty($data['end_date'])) throw ValidationException::withMessages(['end_date'=>'End date is not allowed for relative durations.']);
        } else {
            $data['duration_value'] = null;
        }
        if ($type === 'fixed_dates') {
            if (empty($data['start_date']) || empty($data['end_date'])) throw ValidationException::withMessages(['start_date'=>'Start and end dates are required.']);
            if (Carbon::parse($data['end_date'])->lt(Carbon::parse($data['start_date']))) throw ValidationException::withMessages(['end_date'=>'End date must be on or after start date.']);
        }
        if ($type === 'single_day') {
            if (empty($data['start_date'])) throw ValidationException::withMessages(['start_date'=>'Start date is required.']);
            if (! empty($data['end_date']) && ! Carbon::parse($data['end_date'])->isSameDay(Carbon::parse($data['start_date']))) throw ValidationException::withMessages(['end_date'=>'Single-day end date must equal start date.']);
            $data['end_date'] = $data['start_date'];
        }
        if ($type === 'no_expiry') { $data['duration_value'] = null; $data['end_date'] = null; }
        return $data;
    }
}
