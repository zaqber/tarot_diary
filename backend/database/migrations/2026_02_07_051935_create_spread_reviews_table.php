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
        Schema::create('spread_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('spread_reading_id')->unique()->constrained('spread_readings')->onDelete('cascade');
            $table->timestamp('review_time')->useCurrent();
            $table->integer('overall_accuracy')->nullable();
            $table->text('review_note')->nullable();
            $table->text('lessons_learned')->nullable();
        });

        // SQLite 不支援 ALTER TABLE ADD CONSTRAINT，僅在 MySQL/PostgreSQL 加上 CHECK
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement('ALTER TABLE spread_reviews ADD CONSTRAINT check_overall_accuracy CHECK (overall_accuracy BETWEEN 1 AND 5)');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('spread_reviews');
    }
};
