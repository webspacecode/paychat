<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('tenants', 'settings')) {
            Schema::table('tenants', function (Blueprint $table) {
                $table->json('settings')->nullable();
            });
        }
    }

    public function down(): void
    {
        // Intentionally no-op: tenant settings may already exist in some installs.
    }
};
