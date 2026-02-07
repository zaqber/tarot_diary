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
        Schema::create('reminder_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('reminder_type', 30); // 'morning_draw' or 'evening_review'
            $table->timestamp('scheduled_time');
            $table->timestamp('sent_time')->nullable();
            $table->boolean('is_sent')->default(false);
            $table->boolean('is_clicked')->default(false);
            $table->timestamp('click_time')->nullable();
            
            $table->string('title', 200)->nullable();
            $table->text('message')->nullable();
            
            $table->timestamp('created_at')->useCurrent();
            
            $table->index(['scheduled_time', 'is_sent']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reminder_logs');
    }
};
