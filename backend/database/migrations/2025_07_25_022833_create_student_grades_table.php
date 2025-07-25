<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_grades', function (Blueprint $table) {
            $table->id();
            $table->enum('assessment_type', ['quiz', 'assignment', 'exam']);
            $table->unsignedBigInteger('class_subject_id');
            $table->unsignedBigInteger('student_id')->index(); // foreign key reference
            $table->unsignedBigInteger('term_id');
            $table->string('grade_letter');
            $table->integer('score_obtained');
            $table->integer('max_score');
            $table->float('weightage')->nullable();
            $table->text('remarks')->nullable();
            $table->unsignedBigInteger('recorded_by');
            $table->dateTime('recorded_at');
            $table->timestamps();

            // Foreign keys
            $table->foreign('class_subject_id')->references('id')->on('class_subjects')->onDelete('cascade');
            $table->foreign('student_id')->references('user_id')->on('students')->onDelete('cascade'); // ✅
            $table->foreign('term_id')->references('id')->on('terms')->onDelete('cascade');
            $table->foreign('recorded_by')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_grades');
    }
};
