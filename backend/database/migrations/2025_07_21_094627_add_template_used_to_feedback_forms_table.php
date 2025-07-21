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
        Schema::table('feedback_forms', function (Blueprint $table) {
            $table->string('template_used')->nullable()->after('google_form_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('feedback_forms', function (Blueprint $table) {
            $table->dropColumn('template_used');
        });
    }
};
