<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('table_session_tables')) {
            Schema::create('table_session_tables', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('table_session_id');
                $table->unsignedBigInteger('table_id');
                $table->string('role', 30)->default('linked');
                $table->timestamp('joined_at')->nullable();
                $table->timestamp('released_at')->nullable();
                $table->timestamps();

                $table->index('table_session_id');
                $table->index('table_id');
                $table->index('role');
                $table->index('released_at');
            });
        }

        $now = now();

        DB::table('table_sessions')
            ->whereNotNull('table_id')
            ->orderBy('id')
            ->chunkById(100, function ($sessions) use ($now) {
                foreach ($sessions as $session) {
                    $exists = DB::table('table_session_tables')
                        ->where('table_session_id', $session->id)
                        ->where('table_id', $session->table_id)
                        ->where('role', 'primary')
                        ->exists();

                    if ($exists) {
                        continue;
                    }

                    DB::table('table_session_tables')->insert([
                        'table_session_id' => $session->id,
                        'table_id' => $session->table_id,
                        'role' => 'primary',
                        'joined_at' => $session->opened_at ?? $session->created_at,
                        'released_at' => $session->status === 'closed' ? $session->closed_at : null,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }
            });
    }

    public function down(): void
    {
        Schema::dropIfExists('table_session_tables');
    }
};
