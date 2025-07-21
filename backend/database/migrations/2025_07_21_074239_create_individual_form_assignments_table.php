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
        Schema::create('individual_form_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('feedback_form_id')->constrained('feedback_forms')->onDelete('cascade');
            $table->foreignId('assigned_to_user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('assigned_by_teacher_id')->constrained('users')->onDelete('cascade');
            $table->date('due_date')->nullable();
            $table->boolean('is_active')->default(true);
            $table->text('instructions')->nullable();
            $table->timestamps();
            
            // Prevent duplicate assignments to the same user for the same form
            $table->unique(['feedback_form_id', 'assigned_to_user_id'], 'unique_individual_assignment');
            $table->index(['assigned_to_user_id', 'is_active']);
            $table->index('assigned_by_teacher_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('individual_form_assignments');
    }
};
