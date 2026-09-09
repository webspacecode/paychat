<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $needsEnabled = ! Schema::hasColumn('locations', 'service_charge_enabled');
        $needsType = ! Schema::hasColumn('locations', 'service_charge_type');
        $needsValue = ! Schema::hasColumn('locations', 'service_charge_value');
        $needsDefaultApply = ! Schema::hasColumn('locations', 'service_charge_default_apply');

        if (! $needsEnabled && ! $needsType && ! $needsValue && ! $needsDefaultApply) {
            return;
        }

        Schema::table('locations', function (Blueprint $table) use ($needsEnabled, $needsType, $needsValue, $needsDefaultApply) {
            if ($needsEnabled) {
                $table->boolean('service_charge_enabled')->default(false);
            }

            if ($needsType) {
                $table->string('service_charge_type', 20)->nullable();
            }

            if ($needsValue) {
                $table->decimal('service_charge_value', 12, 2)->nullable();
            }

            if ($needsDefaultApply) {
                $table->boolean('service_charge_default_apply')->default(true);
            }
        });
    }

    public function down(): void
    {
        // Intentionally no-op: outlet billing settings may already be in use by live tenants.
    }
};
