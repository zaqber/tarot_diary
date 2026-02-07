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
        Schema::create('review_tag_matches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('review_id')->constrained('daily_reviews')->onDelete('cascade');
            $table->foreignId('tag_id')->constrained('tags')->onDelete('cascade');
            $table->boolean('is_matched')->default(false);
            $table->string('match_strength', 20)->default('moderate'); // 'weak', 'moderate', 'strong'
            $table->text('specific_example')->nullable();
            
            $table->unique(['review_id', 'tag_id'], 'unique_review_tag');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('review_tag_matches');
    }
};
