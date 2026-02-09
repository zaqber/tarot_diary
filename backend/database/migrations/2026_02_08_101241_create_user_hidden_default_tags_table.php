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
        Schema::create('user_hidden_default_tags', function (Blueprint $table) {
            $table->id();
            $table->foreignId('card_id')->constrained('tarot_cards')->onDelete('cascade');
            $table->foreignId('tag_id')->constrained('tags')->onDelete('cascade');
            $table->string('position', 20)->default('both'); // 'upright', 'reversed', 'both'
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');

            $table->unique(['card_id', 'tag_id', 'position', 'user_id'], 'unique_hidden_default_tag');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_hidden_default_tags');
    }
};
