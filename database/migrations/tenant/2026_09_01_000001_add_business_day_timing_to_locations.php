<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $needsEnabled = ! Schema::hasColumn('locations', 'business_day_enabled');
        $needsStart = ! Schema::hasColumn('locations', 'business_day_start_time');
        $needsEnd = ! Schema::hasColumn('locations', 'business_day_end_time');
        $needsTimezone = ! Schema::hasColumn('locations', 'timezone');

        if (! $needsEnabled && ! $needsStart && ! $needsEnd && ! $needsTimezone) {
            return;
        }

        Schema::table('locations', function (Blueprint $table) use ($needsEnabled, $needsStart, $needsEnd, $needsTimezone) {
            if ($needsEnabled) {
                $table->boolean('business_day_enabled')->nullable()->default(false);
            }

            if ($needsStart) {
                $table->time('business_day_start_time')->nullable();
            }

            if ($needsEnd) {
                $table->time('business_day_end_time')->nullable();
            }

            if ($needsTimezone) {
                $table->string('timezone', 80)->nullable();
            }
        });
    }

    public function down(): void
    {
        // Intentionally no-op: these optional outlet timing columns may be shared by live tenants.
    }
};
