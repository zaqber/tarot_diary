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
        Schema::create('tag_statistics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tag_id')->constrained('tags')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
            
            $table->integer('total_appearances')->default(0);
            $table->integer('total_matches')->default(0);
            $table->decimal('match_rate', 5, 2)->nullable();
            
            $table->timestamp('last_matched_at')->nullable();
            $table->timestamp('last_updated')->useCurrent();
            
            $table->unique(['tag_id', 'user_id'], 'unique_tag_stat');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tag_statistics');
    }
};
