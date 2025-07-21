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
        Schema::table('form_assignments', function (Blueprint $table) {
            $table->datetime('scheduled_date')->nullable()->after('due_date');
            $table->boolean('auto_remind')->default(false)->after('is_active');
            $table->integer('remind_days_before')->default(1)->after('auto_remind');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('form_assignments', function (Blueprint $table) {
            $table->dropColumn(['scheduled_date', 'auto_remind', 'remind_days_before']);
        });
    }
};
