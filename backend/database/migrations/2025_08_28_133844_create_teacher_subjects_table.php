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
        Schema::create('teacher_subjects', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('teacher_id'); // references users.id where role = 'teacher'
            $table->unsignedBigInteger('subject_id');
            $table->boolean('primary_subject')->default(false);
            $table->timestamp('assigned_at')->useCurrent();
            $table->timestamps();
            
            // Foreign key constraints
            $table->foreign('teacher_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('subject_id')->references('id')->on('subjects')->onDelete('cascade');
            
            // Unique constraint to prevent duplicate assignments
            $table->unique(['teacher_id', 'subject_id']);
            
            // Index for better query performance
            $table->index(['teacher_id', 'primary_subject']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teacher_subjects');
    }
};
