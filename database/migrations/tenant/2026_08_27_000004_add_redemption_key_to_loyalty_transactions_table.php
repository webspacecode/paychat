<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('loyalty_transactions', function (Blueprint $table) {
            if (! Schema::hasColumn('loyalty_transactions', 'redemption_key')) {
                $table->string('redemption_key', 64)->nullable()->after('created_by');
                $table->unique('redemption_key', 'loyalty_transactions_redemption_key_unique');
            }
        });
    }

    public function down(): void
    {
        Schema::table('loyalty_transactions', function (Blueprint $table) {
            if (Schema::hasColumn('loyalty_transactions', 'redemption_key')) {
                $table->dropUnique('loyalty_transactions_redemption_key_unique');
                $table->dropColumn('redemption_key');
            }
        });
    }
};
