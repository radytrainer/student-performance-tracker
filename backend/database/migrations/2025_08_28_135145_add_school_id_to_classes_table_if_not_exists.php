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
        Schema::table('classes', function (Blueprint $table) {
            // Add school_id column if it doesn't exist
            if (!Schema::hasColumn('classes', 'school_id')) {
                $table->unsignedBigInteger('school_id')->nullable()->after('schedule');
                $table->foreign('school_id')->references('id')->on('schools')->onDelete('set null');
            }

            // Add indexes for better performance
            if (!Schema::hasColumn('classes', 'class_teacher_id')) {
                // This should already exist but let's be safe
                $table->unsignedBigInteger('class_teacher_id')->nullable();
                $table->foreign('class_teacher_id')->references('user_id')->on('teachers')->onDelete('set null');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('classes', function (Blueprint $table) {
            // Only drop if they exist
            if (Schema::hasColumn('classes', 'school_id')) {
                $table->dropForeign(['school_id']);
                $table->dropColumn('school_id');
            }
        });
    }
};
