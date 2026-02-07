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
        Schema::create('user_monthly_stats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->integer('year');
            $table->integer('month');
            
            $table->integer('total_draws')->default(0);
            $table->integer('total_reviews')->default(0);
            $table->decimal('review_completion_rate', 5, 2)->nullable();
            $table->decimal('average_match_score', 3, 2)->nullable();
            
            $table->foreignId('most_drawn_card_id')->nullable()->constrained('tarot_cards')->onDelete('set null');
            $table->foreignId('highest_match_card_id')->nullable()->constrained('tarot_cards')->onDelete('set null');
            
            $table->unique(['user_id', 'year', 'month'], 'unique_user_month');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_monthly_stats');
    }
};
