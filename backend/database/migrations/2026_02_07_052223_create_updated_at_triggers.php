<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 僅 PostgreSQL 支援此語法；SQLite/MySQL 由 Eloquent 自動維護 updated_at
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        // 創建通用的 updated_at 觸發器函數
        DB::unprepared('
            CREATE OR REPLACE FUNCTION update_updated_at_column()
            RETURNS TRIGGER AS $$
            BEGIN
                NEW.updated_at = CURRENT_TIMESTAMP;
                RETURN NEW;
            END;
            $$ language \'plpgsql\';
        ');

        // 為 tarot_cards 表創建觸發器
        DB::unprepared('
            CREATE TRIGGER update_tarot_cards_updated_at 
            BEFORE UPDATE ON tarot_cards 
            FOR EACH ROW 
            EXECUTE FUNCTION update_updated_at_column();
        ');

        // 為 user_journals 表創建觸發器
        DB::unprepared('
            CREATE TRIGGER update_user_journals_updated_at 
            BEFORE UPDATE ON user_journals 
            FOR EACH ROW 
            EXECUTE FUNCTION update_updated_at_column();
        ');

        // 為 card_statistics 表創建觸發器
        DB::unprepared('
            CREATE TRIGGER update_card_statistics_last_updated 
            BEFORE UPDATE ON card_statistics 
            FOR EACH ROW 
            EXECUTE FUNCTION update_updated_at_column();
        ');

        // 為 tag_statistics 表創建觸發器
        DB::unprepared('
            CREATE TRIGGER update_tag_statistics_last_updated 
            BEFORE UPDATE ON tag_statistics 
            FOR EACH ROW 
            EXECUTE FUNCTION update_updated_at_column();
        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }
        DB::unprepared('DROP TRIGGER IF EXISTS update_tarot_cards_updated_at ON tarot_cards');
        DB::unprepared('DROP TRIGGER IF EXISTS update_user_journals_updated_at ON user_journals');
        DB::unprepared('DROP TRIGGER IF EXISTS update_card_statistics_last_updated ON card_statistics');
        DB::unprepared('DROP TRIGGER IF EXISTS update_tag_statistics_last_updated ON tag_statistics');
        DB::unprepared('DROP FUNCTION IF EXISTS update_updated_at_column()');
    }
};
