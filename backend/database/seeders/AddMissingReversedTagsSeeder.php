<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * 一次性補上所有塔羅牌缺少的逆位標籤（不刪除既有資料）。
 * 執行：php artisan db:seed --class=AddMissingReversedTagsSeeder
 */
class AddMissingReversedTagsSeeder extends Seeder
{
    private function getReversedTagsToAdd(): array
    {
        return [
            // 大阿爾克那
            '女祭司'   => ['困惑', '孤獨'],
            '魔術師'   => ['失衡'],
            '皇后'    => ['焦慮'],
            '皇帝'    => ['混亂'],
            '教皇'    => ['困惑'],
            '戀人'    => ['失衡'],
            '戰車'    => ['失衡'],
            '力量'    => ['焦慮'],
            '隱者'    => ['困惑'],
            '命運之輪'  => ['挫折'],
            '正義'    => ['失衡'],
            '倒吊者'   => ['焦慮'],
            '死神'    => ['等待'],
            '節制'    => ['焦慮'],
            '惡魔'    => ['恐懼'],
            '高塔'    => ['挫折'],
            '星星'    => ['焦慮'],
            '月亮'    => ['希望'],
            '太陽'    => ['挫折'],
            '審判'    => ['困惑'],
            '世界'    => ['挫折'],

            // 權杖
            '權杖王牌'  => ['恐懼', '等待'],
            '權杖二'   => ['挫折'],
            '權杖三'   => ['挫折', '等待'],
            '權杖四'   => ['衝突', '失衡'],
            '權杖五'   => ['和解'],
            '權杖六'   => ['挫折', '困惑'],
            '權杖七'   => ['焦慮'],
            '權杖八'   => ['等待', '混亂'],
            '權杖九'   => ['挫折'],
            '權杖十'   => ['挫折', '失落'],
            '權杖侍者'  => ['混亂', '困惑'],
            '權杖騎士'  => ['混亂', '衝突'],
            '權杖王后'  => ['失衡', '緊張'],
            '權杖國王'  => ['混亂', '緊張'],

            // 聖杯
            '聖杯王牌'  => ['悲傷', '困惑'],
            '聖杯二'   => ['衝突', '失衡'],
            '聖杯三'   => ['孤獨', '衝突'],
            '聖杯四'   => ['行動', '新開始'],
            '聖杯五'   => ['希望'],
            '聖杯六'   => ['困惑', '焦慮'],
            '聖杯七'   => ['行動', '決定'],
            '聖杯八'   => ['等待', '焦慮'],
            '聖杯九'   => ['失落', '焦慮'],
            '聖杯十'   => ['衝突', '失落'],
            '聖杯侍者'  => ['恐懼', '困惑'],
            '聖杯騎士'  => ['困惑', '逃避'],
            '聖杯王后'  => ['失衡', '焦慮'],
            '聖杯國王'  => ['衝突', '緊張'],

            // 寶劍
            '寶劍王牌'  => ['混亂', '困惑'],
            '寶劍二'   => ['恐懼', '焦慮'],
            '寶劍三'   => ['希望'],
            '寶劍四'   => ['焦慮', '行動'],
            '寶劍五'   => ['挫折'],
            '寶劍六'   => ['等待', '挫折'],
            '寶劍七'   => ['困惑'],
            '寶劍八'   => ['行動'],
            '寶劍九'   => ['療癒', '希望'],
            '寶劍十'   => ['希望'],
            '寶劍侍者'  => ['衝突', '困惑'],
            '寶劍騎士'  => ['混亂', '等待'],
            '寶劍王后'  => ['緊張', '孤獨'],
            '寶劍國王'  => ['緊張', '衝突'],

            // 錢幣
            '錢幣王牌'  => ['挫折', '等待'],
            '錢幣二'   => ['焦慮'],
            '錢幣三'   => ['衝突', '挫折'],
            '錢幣四'   => ['失衡'],
            '錢幣五'   => ['希望'],
            '錢幣六'   => ['失衡', '衝突'],
            '錢幣七'   => ['焦慮', '挫折'],
            '錢幣八'   => ['挫折', '失衡'],
            '錢幣九'   => ['失落', '焦慮'],
            '錢幣十'   => ['失衡', '衝突'],
            '錢幣侍者'  => ['等待', '挫折'],
            '錢幣騎士'  => ['挫折', '失衡'],
            '錢幣王后'  => ['失衡', '焦慮'],
            '錢幣國王'  => ['失衡', '混亂'],
        ];
    }

    private function getTagAliases(): array
    {
        return [
            '逃避' => '恐懼',
        ];
    }

    public function run(): void
    {
        $cards   = DB::table('tarot_cards')->get()->keyBy('name_zh');
        $tags    = DB::table('tags')->get()->keyBy('name_zh');
        $aliases = $this->getTagAliases();
        $toAdd   = $this->getReversedTagsToAdd();

        $inserted = 0;
        $skipped  = 0;

        foreach ($toAdd as $cardNameZh => $tagNames) {
            $card = $cards->get($cardNameZh);
            if (!$card) {
                $this->command->warn("找不到牌：{$cardNameZh}");
                continue;
            }

            foreach ($tagNames as $tagNameZh) {
                $resolved = $aliases[$tagNameZh] ?? $tagNameZh;
                $tag = $tags->get($resolved);
                if (!$tag) {
                    $this->command->warn("找不到標籤：{$tagNameZh}（resolved: {$resolved}）");
                    continue;
                }

                // 已存在則跳過
                $exists = DB::table('card_tags')
                    ->where('card_id', $card->id)
                    ->where('tag_id', $tag->id)
                    ->where('position', 'reversed')
                    ->whereNull('user_id')
                    ->exists();

                if ($exists) {
                    $skipped++;
                    continue;
                }

                DB::table('card_tags')->insert([
                    'card_id'    => $card->id,
                    'tag_id'     => $tag->id,
                    'position'   => 'reversed',
                    'is_default' => true,
                    'user_id'    => null,
                ]);
                $inserted++;
            }
        }

        $this->command->info("已新增 {$inserted} 筆逆位標籤，略過重複 {$skipped} 筆。");
    }
}
