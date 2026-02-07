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
        Schema::create('card_tags', function (Blueprint $table) {
            $table->id();
            $table->foreignId('card_id')->constrained('tarot_cards')->onDelete('cascade');
            $table->foreignId('tag_id')->constrained('tags')->onDelete('cascade');
            $table->string('position', 20)->default('both'); // 'upright', 'reversed', 'both'
            $table->boolean('is_default')->default(true);
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
            
            $table->unique(['card_id', 'tag_id', 'position', 'user_id'], 'unique_card_tag');
            $table->index('card_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('card_tags');
    }
};
