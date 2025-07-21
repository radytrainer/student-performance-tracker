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
        Schema::create('feedback_forms', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('google_form_id')->nullable();
            $table->string('google_form_url')->nullable();
            $table->foreignId('created_by_teacher_id')->constrained('users')->onDelete('cascade');
            $table->boolean('is_active')->default(true);
            $table->enum('survey_type', ['weekly', 'monthly'])->default('monthly');
            $table->json('questions')->nullable(); // Store form questions as JSON
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->timestamps();
            
            $table->index(['created_by_teacher_id', 'is_active']);
            $table->index('survey_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('feedback_forms');
    }
};
