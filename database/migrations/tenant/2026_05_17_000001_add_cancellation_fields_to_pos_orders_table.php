<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pos_orders', function (Blueprint $table) {
            if (!Schema::hasColumn('pos_orders', 'cancelled_at')) {
                $table->timestamp('cancelled_at')->nullable();
            }

            if (!Schema::hasColumn('pos_orders', 'cancelled_by')) {
                $table->unsignedBigInteger('cancelled_by')->nullable();
            }

            if (!Schema::hasColumn('pos_orders', 'cancel_reason_type')) {
                $table->string('cancel_reason_type', 100)->nullable();
            }

            if (!Schema::hasColumn('pos_orders', 'cancel_reason')) {
                $table->text('cancel_reason')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('pos_orders', function (Blueprint $table) {
            if (Schema::hasColumn('pos_orders', 'cancel_reason')) {
                $table->dropColumn('cancel_reason');
            }

            if (Schema::hasColumn('pos_orders', 'cancel_reason_type')) {
                $table->dropColumn('cancel_reason_type');
            }
        });
    }
};
