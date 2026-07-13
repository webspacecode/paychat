<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('programs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->unique()->constrained('products')->restrictOnDelete();
            $table->string('program_type', 50)->nullable()->index();
            $table->text('description')->nullable();
            $table->string('duration_type', 40)->index();
            $table->unsignedInteger('duration_value')->nullable();
            $table->date('start_date')->nullable()->index();
            $table->date('end_date')->nullable()->index();
            $table->date('registration_open_date')->nullable();
            $table->date('registration_close_date')->nullable();
            $table->unsignedInteger('capacity')->nullable();
            $table->boolean('renewable')->default(false);
            $table->string('renewal_frequency', 40)->nullable();
            $table->string('status', 30)->default('draft')->index();
            $table->json('settings')->nullable();
            $table->unsignedBigInteger('created_by')->nullable()->index();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->dateTime('archived_at')->nullable()->index();
            $table->timestamps();
        });

        Schema::create('program_batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_id')->constrained('programs')->restrictOnDelete();
            $table->string('name', 150);
            $table->text('description')->nullable();
            $table->date('start_date')->nullable()->index();
            $table->date('end_date')->nullable()->index();
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->json('days_of_week')->nullable();
            $table->unsignedInteger('capacity')->nullable();
            $table->foreignId('location_id')->nullable()->constrained('locations')->restrictOnDelete();
            $table->string('status', 30)->default('active')->index();
            $table->json('settings')->nullable();
            $table->unsignedBigInteger('created_by')->nullable()->index();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->dateTime('archived_at')->nullable()->index();
            $table->timestamps();
            $table->index(['program_id', 'status']);
        });

        Schema::create('program_batch_instructors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_batch_id')->constrained('program_batches')->cascadeOnDelete();
            $table->unsignedBigInteger('user_id')->index();
            $table->timestamps();
            $table->unique(['program_batch_id', 'user_id'], 'batch_instructor_unique');
        });

        Schema::create('participant_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('pos_customers')->restrictOnDelete();
            $table->string('participant_code', 40)->unique();
            $table->string('first_name', 100);
            $table->string('last_name', 100)->nullable();
            $table->string('display_name', 210)->nullable();
            $table->date('date_of_birth')->nullable()->index();
            $table->string('gender', 30)->nullable();
            $table->string('participant_phone', 50)->nullable()->index();
            $table->string('participant_email', 150)->nullable();
            $table->string('guardian_name', 150)->nullable();
            $table->string('guardian_phone', 50)->nullable();
            $table->string('emergency_contact', 100)->nullable();
            $table->string('school_or_college', 200)->nullable();
            $table->string('occupation', 150)->nullable();
            $table->string('photo_path', 500)->nullable();
            $table->text('notes')->nullable();
            $table->json('custom_data')->nullable();
            $table->string('status', 30)->default('active')->index();
            $table->unsignedBigInteger('created_by')->nullable()->index();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->dateTime('archived_at')->nullable()->index();
            $table->timestamps();
            $table->index(['customer_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('participant_profiles');
        Schema::dropIfExists('program_batch_instructors');
        Schema::dropIfExists('program_batches');
        Schema::dropIfExists('programs');
    }
};
