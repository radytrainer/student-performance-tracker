<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('departments', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->unsignedBigInteger('head_teacher_id')->nullable();
            $table->timestamps();

            $table->index('head_teacher_id');
            $table->unique('name');
        });
    }

    public function down()
    {
        Schema::dropIfExists('departments');
    }
};
