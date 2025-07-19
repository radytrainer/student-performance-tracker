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
        Schema::create('student_feedback', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('teacher_id');
            $table->unsignedBigInteger('term_id');
            $table->tinyInteger('question_1')->unsigned()->min(1)->max(10);
            $table->tinyInteger('question_2')->unsigned()->min(1)->max(10);
            $table->tinyInteger('question_3')->unsigned()->min(1)->max(10);
            $table->tinyInteger('question_4')->unsigned()->min(1)->max(10);
            $table->tinyInteger('question_5')->unsigned()->min(1)->max(10);
            $table->decimal('overall_rating', 3, 2)->nullable();
            $table->text('comments')->nullable();
            $table->date('feedback_date');
            $table->timestamps();

            $table->foreign('student_id')->references('user_id')->on('students')->onDelete('cascade');
            $table->foreign('teacher_id')->references('user_id')->on('teachers')->onDelete('cascade');
            $table->foreign('term_id')->references('id')->on('terms')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_feedback');
    }
};
