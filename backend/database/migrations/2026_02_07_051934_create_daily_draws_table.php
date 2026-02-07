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
        Schema::create('daily_draws', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->date('draw_date');
            $table->foreignId('card_id')->constrained('tarot_cards')->onDelete('restrict');
            $table->boolean('is_reversed')->default(false);
            $table->timestamp('draw_time')->useCurrent();
            $table->text('morning_note')->nullable();
            $table->string('mood_before', 50)->nullable();
            
            $table->unique(['user_id', 'draw_date'], 'unique_daily_draw');
            $table->index(['user_id', 'draw_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_draws');
    }
};
