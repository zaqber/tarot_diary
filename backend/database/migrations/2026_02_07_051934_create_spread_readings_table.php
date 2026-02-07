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
        Schema::create('spread_readings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('spread_type_id')->constrained('spread_types')->onDelete('restrict');
            $table->date('reading_date');
            $table->timestamp('reading_time')->useCurrent();
            $table->text('question')->nullable();
            $table->text('overall_note')->nullable();
            $table->boolean('is_reviewed')->default(false);
            
            $table->index(['user_id', 'reading_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('spread_readings');
    }
};
