<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('upi_profiles')) {
            Schema::create('upi_profiles', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('location_id')->nullable();
                $table->string('label');
                $table->string('upi_id');
                $table->string('payee_name')->nullable();
                $table->boolean('is_default')->default(false);
                $table->boolean('is_active')->default(true);
                $table->integer('sort_order')->default(0);
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->index('location_id');
                $table->index('is_active');
                $table->index(['location_id', 'is_default']);
            });
        }

        Schema::table('pos_payments', function (Blueprint $table) {
            if (!Schema::hasColumn('pos_payments', 'upi_profile_id')) {
                $table->unsignedBigInteger('upi_profile_id')->nullable();
                $table->index('upi_profile_id');
            }

            if (!Schema::hasColumn('pos_payments', 'meta')) {
                $table->json('meta')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('pos_payments', function (Blueprint $table) {
            if (Schema::hasColumn('pos_payments', 'upi_profile_id')) {
                $table->dropIndex(['upi_profile_id']);
                $table->dropColumn('upi_profile_id');
            }
        });

        Schema::dropIfExists('upi_profiles');
    }
};
