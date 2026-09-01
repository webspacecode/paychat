<?php

namespace App\Services;

use App\Models\Tenant\Location;
use App\Models\Tenant\Setting;
use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;
use Throwable;

class BusinessDayService
{
    public function forLocation(?int $locationId = null, mixed $timestamp = null): string
    {
        $dateTime = $this->parseTimestamp($timestamp);
        $location = $this->location($locationId);

        if (! $this->hasLocationTiming($location)) {
            return $dateTime->toDateString();
        }

        $timezone = $this->timezone($location);
        $local = $dateTime->copy()->setTimezone($timezone);
        $startTime = $this->normalizeTime($location->business_day_start_time);
        $endTime = $this->normalizeTime($location->business_day_end_time);

        if (! $startTime || ! $endTime) {
            return $dateTime->toDateString();
        }

        $start = Carbon::parse($local->toDateString().' '.$startTime, $timezone);
        $end = Carbon::parse($local->toDateString().' '.$endTime, $timezone);

        if ($end->lte($start) && $local->lt($end)) {
            return $local->copy()->subDay()->toDateString();
        }

        return $local->toDateString();
    }

    public function currentForLocation(?int $locationId = null): string
    {
        return $this->forLocation($locationId, now());
    }

    public function previousForLocation(?int $locationId = null): string
    {
        return Carbon::parse($this->currentForLocation($locationId))->subDay()->toDateString();
    }

    public function configuredForLocation(?int $locationId = null): bool
    {
        return $this->hasLocationTiming($this->location($locationId));
    }

    public function manualBusinessDate(): ?string
    {
        $date = Setting::get('current_business_date')
            ?? Setting::get('business_date')
            ?? Setting::get('shift_date');

        return $date ? Carbon::parse($date)->toDateString() : null;
    }

    public function legacyStartBusinessDate(): ?string
    {
        $businessDayStart = Setting::get('business_day_start_time')
            ?? Setting::get('day_start_time');

        if (! $businessDayStart) {
            return null;
        }

        $now = now();
        $start = Carbon::parse($now->toDateString().' '.$businessDayStart);

        return $now->lt($start)
            ? $now->copy()->subDay()->toDateString()
            : $now->toDateString();
    }

    private function location(?int $locationId): ?Location
    {
        if (! $locationId || ! Schema::hasTable('locations')) {
            return null;
        }

        try {
            return Location::find($locationId);
        } catch (Throwable) {
            return null;
        }
    }

    private function hasLocationTiming(?Location $location): bool
    {
        if (! $location) {
            return false;
        }

        foreach (['business_day_enabled', 'business_day_start_time', 'business_day_end_time'] as $column) {
            if (! Schema::hasColumn('locations', $column)) {
                return false;
            }
        }

        return $location->business_day_enabled === true
            && filled($location->business_day_start_time)
            && filled($location->business_day_end_time);
    }

    private function parseTimestamp(mixed $timestamp): Carbon
    {
        if ($timestamp instanceof Carbon) {
            return $timestamp->copy();
        }

        return $timestamp ? Carbon::parse($timestamp) : now();
    }

    private function timezone(?Location $location): string
    {
        if ($location && Schema::hasColumn('locations', 'timezone') && filled($location->timezone)) {
            return (string) $location->timezone;
        }

        return config('app.timezone', 'UTC');
    }

    private function normalizeTime(mixed $time): ?string
    {
        if (! filled($time)) {
            return null;
        }

        return Carbon::parse((string) $time)->format('H:i:s');
    }
}
