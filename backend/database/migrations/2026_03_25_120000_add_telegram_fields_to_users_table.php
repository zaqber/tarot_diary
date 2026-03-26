<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('telegram_chat_id', 32)->nullable()->after('is_evening_reminder_enabled');
            $table->string('telegram_link_token', 64)->nullable()->after('telegram_chat_id');
            $table->timestamp('telegram_link_token_expires_at')->nullable()->after('telegram_link_token');

            $table->index('telegram_link_token');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['telegram_link_token']);
            $table->dropColumn([
                'telegram_chat_id',
                'telegram_link_token',
                'telegram_link_token_expires_at',
            ]);
        });
    }
};
