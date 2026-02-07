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
        Schema::create('spread_cards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('spread_reading_id')->constrained('spread_readings')->onDelete('cascade');
            $table->integer('position_number');
            $table->foreignId('card_id')->constrained('tarot_cards')->onDelete('restrict');
            $table->boolean('is_reversed')->default(false);
            $table->text('interpretation')->nullable();
            
            $table->unique(['spread_reading_id', 'position_number'], 'unique_spread_card');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('spread_cards');
    }
};
