<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 按照依賴順序執行 Seeder
        // 1. 首先創建獨立的基礎資料
        $this->call([
            SuitsSeeder::class,          // 花色（獨立）
            TagsSeeder::class,           // 標籤（獨立）
        ]);

        // 2. 創建依賴花色的塔羅牌資料
        $this->call([
            TarotCardsSeeder::class,     // 塔羅牌（依賴 suits）
        ]);

        // 3. 創建牌陣資料
        $this->call([
            SpreadTypesSeeder::class,    // 牌陣類型和位置
        ]);

        // 4. 建立牌卡-標籤關聯
        $this->call([
            CardTagsSeeder::class,  // 牌卡與標籤的關聯（card_tags）
        ]);

        $this->command->info('✅ 所有初始資料已成功填充！');
        $this->command->info('📊 資料統計：');
        $this->command->info('   - 4 個花色');
        $this->command->info('   - 78 張塔羅牌');
        $this->command->info('   - ' . \DB::table('tags')->count() . ' 個標籤');
        $this->command->info('   - ' . \DB::table('card_tags')->count() . ' 個牌卡-標籤關聯');
        $this->command->info('   - ' . \DB::table('spread_types')->count() . ' 種牌陣');
    }
}
