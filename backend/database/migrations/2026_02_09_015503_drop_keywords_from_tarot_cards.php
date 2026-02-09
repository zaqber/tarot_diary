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
        Schema::table('tarot_cards', function (Blueprint $table) {
            $table->dropColumn(['keywords_upright', 'keywords_reversed']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tarot_cards', function (Blueprint $table) {
            $table->string('keywords_upright', 255)->nullable();
            $table->string('keywords_reversed', 255)->nullable();
        });
    }
};
