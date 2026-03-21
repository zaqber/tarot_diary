<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * 為「官方牌義仍為空」的小阿爾克那補上繁體中文牌義。
 * 已有資料的資料庫請執行：php artisan db:seed --class=UpdateMinorArcanaMeaningsSeeder
 */
class UpdateMinorArcanaMeaningsSeeder extends Seeder
{
    public function run(): void
    {
        $minorZh = require __DIR__.'/data/minor_arcana_meanings_zh.php';

        $rows = DB::table('tarot_cards as c')
            ->join('suits as s', 'c.suit_id', '=', 's.id')
            ->where('c.card_type', 'minor')
            ->select('c.id', 'c.number', 's.name_zh as suit_zh', 'c.official_meaning_upright')
            ->get();

        $updated = 0;
        foreach ($rows as $row) {
            $m = $minorZh[$row->suit_zh][$row->number] ?? null;
            if (! $m) {
                continue;
            }
            if (trim((string) $row->official_meaning_upright) !== '') {
                continue;
            }
            DB::table('tarot_cards')->where('id', $row->id)->update([
                'official_meaning_upright' => $m['official_meaning_upright'],
                'official_meaning_reversed' => $m['official_meaning_reversed'],
                'updated_at' => now(),
            ]);
            $updated++;
        }

        $this->command?->info("已更新 {$updated} 張小阿爾克那的官方牌義。");
    }
}
