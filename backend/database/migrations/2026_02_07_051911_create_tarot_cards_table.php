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
        Schema::create('tarot_cards', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('name_zh', 100);
            $table->string('card_type', 20); // 'major' or 'minor'
            $table->foreignId('suit_id')->nullable()->constrained('suits')->onDelete('restrict');
            $table->integer('number');
            
            $table->text('official_meaning_upright')->nullable();
            $table->text('official_meaning_reversed')->nullable();
            $table->text('self_definition_upright')->nullable();
            $table->text('self_definition_reversed')->nullable();
            
            $table->string('keywords_upright', 255)->nullable();
            $table->string('keywords_reversed', 255)->nullable();
            
            $table->string('image_url', 255)->nullable();
            $table->string('image_url_reversed', 255)->nullable();
            
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tarot_cards');
    }
};
