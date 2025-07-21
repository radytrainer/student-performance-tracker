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
        Schema::create('form_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('feedback_form_id')->constrained('feedback_forms')->onDelete('cascade');
            $table->foreignId('student_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('form_assignment_id')->constrained('form_assignments')->onDelete('cascade');
            $table->string('google_response_id')->nullable();
            $table->json('response_data'); // Store all form responses as JSON
            $table->decimal('score_total', 5, 2)->nullable(); // Total score (calculated from responses)
            $table->decimal('average_score', 5, 2)->nullable(); // Average score across questions
            $table->timestamp('submitted_at');
            $table->timestamps();
            
            // Prevent duplicate responses from same student for same form
            $table->unique(['feedback_form_id', 'student_id', 'form_assignment_id'], 'unique_student_response');
            $table->index(['feedback_form_id', 'submitted_at']);
            $table->index('student_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('form_responses');
    }
};
