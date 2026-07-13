<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('program_registrations', function (Blueprint $table) {
            $table->foreignId('order_id')
                ->nullable()
                ->after('program_batch_id')
                ->constrained('pos_orders')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('program_registrations', function (Blueprint $table) {
            $table->dropConstrainedForeignId('order_id');
        });
    }
};
