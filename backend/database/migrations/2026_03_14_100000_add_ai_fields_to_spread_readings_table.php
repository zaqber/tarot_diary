<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('spread_readings', function (Blueprint $table) {
            $table->text('ai_question')->nullable();
            $table->longText('ai_interpretation')->nullable();
            $table->timestamp('ai_generated_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('spread_readings', function (Blueprint $table) {
            $table->dropColumn(['ai_question', 'ai_interpretation', 'ai_generated_at']);
        });
    }
};
