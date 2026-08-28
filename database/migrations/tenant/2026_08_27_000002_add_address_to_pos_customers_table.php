<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pos_customers', function (Blueprint $table) {
            if (! Schema::hasColumn('pos_customers', 'address')) {
                $table->string('address')->nullable()->after('email');
            }
        });
    }

    public function down(): void
    {
        Schema::table('pos_customers', function (Blueprint $table) {
            if (Schema::hasColumn('pos_customers', 'address')) {
                $table->dropColumn('address');
            }
        });
    }
};
