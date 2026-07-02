<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kitchen_batches', function (Blueprint $table) {
            if (! Schema::hasColumn('kitchen_batches', 'dispatch_channel')) {
                $table->string('dispatch_channel', 30)->default('board')->after('status');
                $table->index('dispatch_channel', 'kitchen_batches_dispatch_channel_index');
            }
        });
    }

    public function down(): void
    {
        Schema::table('kitchen_batches', function (Blueprint $table) {
            if (Schema::hasColumn('kitchen_batches', 'dispatch_channel')) {
                $table->dropIndex('kitchen_batches_dispatch_channel_index');
                $table->dropColumn('dispatch_channel');
            }
        });
    }
};
