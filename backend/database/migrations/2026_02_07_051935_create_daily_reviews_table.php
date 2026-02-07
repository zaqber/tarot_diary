<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('daily_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('daily_draw_id')->unique()->constrained('daily_draws')->onDelete('cascade');
            $table->timestamp('review_time')->useCurrent();
            $table->integer('overall_match_score')->nullable();
            $table->text('review_note')->nullable();
            $table->string('mood_after', 50)->nullable();
            $table->text('key_events')->nullable();
            $table->boolean('is_completed')->default(true);
            
            $table->index('review_time');
        });
        
        // Add CHECK constraint for overall_match_score
        DB::statement('ALTER TABLE daily_reviews ADD CONSTRAINT check_overall_match_score CHECK (overall_match_score BETWEEN 1 AND 5)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_reviews');
    }
};
