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
        Schema::create('grades', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('class_subject_id');
            $table->unsignedBigInteger('term_id');
            $table->enum('assessment_type', ['exam', 'quiz', 'assignment', 'project', 'participation']);
            $table->decimal('max_score', 5, 2);
            $table->decimal('score_obtained', 5, 2);
            $table->decimal('weightage', 5, 2)->default(1.00);
            $table->string('grade_letter')->nullable();
            $table->text('remarks')->nullable();
            $table->unsignedBigInteger('recorded_by');
            $table->timestamp('recorded_at');
            $table->timestamps();
            
            $table->foreign('student_id')->references('user_id')->on('students')->onDelete('cascade');
            $table->foreign('class_subject_id')->references('id')->on('class_subjects')->onDelete('cascade');
            $table->foreign('term_id')->references('id')->on('terms')->onDelete('cascade');
            $table->foreign('recorded_by')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('grades');
    }
};
