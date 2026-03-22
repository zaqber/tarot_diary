<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * 抽牌主題：整體 overall、感情 love、事業 career、財務 finance
     */
    public function up(): void
    {
        Schema::table('spread_readings', function (Blueprint $table) {
            $table->string('theme', 32)->default('overall');
        });
    }

    public function down(): void
    {
        Schema::table('spread_readings', function (Blueprint $table) {
            $table->dropColumn('theme');
        });
    }
};
