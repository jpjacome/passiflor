<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('therapist_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('patient_id')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('start_time');
            $table->dateTime('end_time');
            $table->integer('duration')->default(60); // minutes
            $table->string('title');
            $table->string('type')->default('session'); // session, consultation, evaluation, follow-up
            $table->enum('status', ['scheduled', 'confirmed', 'completed', 'cancelled', 'no-show'])->default('scheduled');
            $table->text('notes')->nullable();
            $table->string('color')->default('#A1966B'); // default color
            $table->foreignId('consultation_id')->nullable()->constrained()->nullOnDelete(); // link to consultation if created from one
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
