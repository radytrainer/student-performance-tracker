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
        Schema::create('classes', function (Blueprint $table) {
            $table->id();
            $table->string('class_name');
            $table->string('academic_year');
            $table->unsignedBigInteger('class_teacher_id')->nullable();
            $table->string('room_number')->nullable();
            $table->text('schedule')->nullable();
            $table->timestamps();
            
            $table->foreign('class_teacher_id')->references('user_id')->on('teachers')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('classes');
    }
};
