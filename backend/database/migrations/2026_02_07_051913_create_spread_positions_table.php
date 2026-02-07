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
        Schema::create('spread_positions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('spread_type_id')->constrained('spread_types')->onDelete('cascade');
            $table->integer('position_number');
            $table->string('position_name', 100);
            $table->string('position_name_zh', 100)->nullable();
            $table->text('description')->nullable();
            
            $table->unique(['spread_type_id', 'position_number'], 'unique_position');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('spread_positions');
    }
};
