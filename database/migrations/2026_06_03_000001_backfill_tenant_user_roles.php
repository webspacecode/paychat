<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('users')
            ->whereNotNull('tenant_id')
            ->where(function ($query) {
                $query->whereNull('role')
                    ->orWhere('role', '');
            })
            ->update(['role' => 'owner']);
    }

    public function down(): void
    {
        //
    }
};
