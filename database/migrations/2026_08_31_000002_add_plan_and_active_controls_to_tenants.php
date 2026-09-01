<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $needsPlan = ! Schema::hasColumn('tenants', 'plan');
        $needsActiveFlag = ! Schema::hasColumn('tenants', 'is_active');

        if ($needsPlan || $needsActiveFlag) {
            Schema::table('tenants', function (Blueprint $table) use ($needsPlan, $needsActiveFlag) {
                if ($needsPlan) {
                    $table->string('plan')->nullable();
                }

                if ($needsActiveFlag) {
                    $table->boolean('is_active')->default(true);
                }
            });
        }

        if (Schema::hasColumn('tenants', 'is_active')) {
            DB::table('tenants')->whereNull('is_active')->update(['is_active' => true]);
        }
    }

    public function down(): void
    {
        // Intentionally no-op: these tenant controls may already exist in older installations.
    }
};
