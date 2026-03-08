<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 修正 PostgreSQL users 表 id 序列，避免重複主鍵（id 已存在時序列未同步）
     */
    public function up(): void
    {
        $driver = DB::getDriverName();
        if ($driver !== 'pgsql') {
            return;
        }
        DB::statement(
            "SELECT setval(pg_get_serial_sequence('users', 'id'), COALESCE((SELECT MAX(id) FROM users), 1))"
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 無法安全還原序列，留空
    }
};
