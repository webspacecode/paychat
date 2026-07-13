<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('program_registrations', function (Blueprint $table) {
            $table->id();
            $table->string('registration_number', 40)->unique();
            $table->foreignId('participant_profile_id')->constrained('participant_profiles')->restrictOnDelete();
            $table->foreignId('program_id')->constrained('programs')->restrictOnDelete();
            $table->foreignId('program_batch_id')->nullable()->constrained('program_batches')->restrictOnDelete();
            $table->date('registered_on')->index();
            $table->date('starts_on')->nullable()->index();
            $table->date('ends_on')->nullable()->index();
            $table->decimal('fee_amount', 12, 2)->default(0);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->decimal('final_amount', 12, 2)->default(0);
            $table->string('status', 30)->default('active')->index();
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by')->nullable()->index();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->dateTime('cancelled_at')->nullable()->index();
            $table->timestamps();

            $table->index(['program_id', 'status']);
            $table->index(['program_batch_id', 'status']);
            $table->index(['participant_profile_id', 'status'], 'participant_registration_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('program_registrations');
    }
};
