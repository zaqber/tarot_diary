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
        Schema::create('spread_types', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('name_zh', 100);
            $table->text('description')->nullable();
            $table->integer('card_count');
            $table->string('difficulty_level', 20)->default('beginner'); // 'beginner', 'intermediate', 'advanced'
            $table->string('image_url', 255)->nullable();
            $table->boolean('is_active')->default(true);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('spread_types');
    }
};
