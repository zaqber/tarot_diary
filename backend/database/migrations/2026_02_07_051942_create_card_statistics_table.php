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
        Schema::create('card_statistics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('card_id')->constrained('tarot_cards')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->string('position', 20); // 'upright' or 'reversed'
            
            $table->integer('total_draws')->default(0);
            $table->integer('total_reviews')->default(0);
            $table->integer('total_match_score')->default(0);
            $table->decimal('average_match_score', 3, 2)->nullable();
            
            $table->timestamp('last_drawn_at')->nullable();
            $table->timestamp('last_updated')->useCurrent();
            
            $table->unique(['card_id', 'user_id', 'position'], 'unique_card_stat');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('card_statistics');
    }
};
