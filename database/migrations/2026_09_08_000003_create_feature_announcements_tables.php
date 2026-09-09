<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('feature_announcements', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('title');
            $table->text('description');
            $table->string('action_label')->nullable();
            $table->string('action_url')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('published_at')->nullable()->index();
            $table->timestamps();

            $table->index(['is_active', 'published_at']);
        });

        Schema::create('user_feature_announcements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('feature_announcement_id')->constrained('feature_announcements')->restrictOnDelete();
            $table->timestamp('seen_at')->nullable();
            $table->timestamp('action_clicked_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'feature_announcement_id'], 'user_feature_announcement_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_feature_announcements');
        Schema::dropIfExists('feature_announcements');
    }
};
