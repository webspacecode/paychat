<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pos_customers', function (Blueprint $table) {
            if (! Schema::hasColumn('pos_customers', 'total_visits')) {
                $table->integer('total_visits')->default(0)->after('loyalty_points');
            }

            if (! Schema::hasColumn('pos_customers', 'total_spend')) {
                $table->decimal('total_spend', 15, 2)->default(0)->after('total_visits');
            }

            if (! Schema::hasColumn('pos_customers', 'last_visit_at')) {
                $table->timestamp('last_visit_at')->nullable()->after('total_spend');
            }
        });
    }

    public function down(): void
    {
        Schema::table('pos_customers', function (Blueprint $table) {
            if (Schema::hasColumn('pos_customers', 'last_visit_at')) {
                $table->dropColumn('last_visit_at');
            }

            if (Schema::hasColumn('pos_customers', 'total_spend')) {
                $table->dropColumn('total_spend');
            }

            if (Schema::hasColumn('pos_customers', 'total_visits')) {
                $table->dropColumn('total_visits');
            }
        });
    }
};
