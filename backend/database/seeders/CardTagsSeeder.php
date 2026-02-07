<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CardTagsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * 為塔羅牌建立標籤關聯的範例
     * 展示如何為不同的牌配置正位/逆位的標籤
     */
    public function run(): void
    {
        // 獲取卡片和標籤
        $cards = DB::table('tarot_cards')->get()->keyBy('name_zh');
        $tags = DB::table('tags')->get()->keyBy('name_zh');

        $cardTags = [];

        // ============================================
        // 範例 1: 愚者 (The Fool)
        // ============================================
        if (isset($cards['愚者'])) {
            $foolId = $cards['愚者']->id;
            
            // 正位標籤
            if (isset($tags['新開始'])) {
                $cardTags[] = [
                    'card_id' => $foolId,
                    'tag_id' => $tags['新開始']->id,
                    'position' => 'upright',
                    'is_default' => true,
                    'user_id' => null,
                ];
            }
            if (isset($tags['希望'])) {
                $cardTags[] = [
                    'card_id' => $foolId,
                    'tag_id' => $tags['希望']->id,
                    'position' => 'upright',
                    'is_default' => true,
                    'user_id' => null,
                ];
            }
            if (isset($tags['冒險'])) {
                $cardTags[] = [
                    'card_id' => $foolId,
                    'tag_id' => $tags['冒險']->id,
                    'position' => 'upright',
                    'is_default' => true,
                    'user_id' => null,
                ];
            }
            
            // 逆位標籤
            if (isset($tags['焦慮'])) {
                $cardTags[] = [
                    'card_id' => $foolId,
                    'tag_id' => $tags['焦慮']->id,
                    'position' => 'reversed',
                    'is_default' => true,
                    'user_id' => null,
                ];
            }
            if (isset($tags['挫折'])) {
                $cardTags[] = [
                    'card_id' => $foolId,
                    'tag_id' => $tags['挫折']->id,
                    'position' => 'reversed',
                    'is_default' => true,
                    'user_id' => null,
                ];
            }
        }

        // ============================================
        // 範例 2: 戀人 (The Lovers)
        // ============================================
        if (isset($cards['戀人'])) {
            $loversId = $cards['戀人']->id;
            
            // 正位標籤
            if (isset($tags['愛'])) {
                $cardTags[] = [
                    'card_id' => $loversId,
                    'tag_id' => $tags['愛']->id,
                    'position' => 'upright',
                    'is_default' => true,
                    'user_id' => null,
                ];
            }
            if (isset($tags['浪漫'])) {
                $cardTags[] = [
                    'card_id' => $loversId,
                    'tag_id' => $tags['浪漫']->id,
                    'position' => 'upright',
                    'is_default' => true,
                    'user_id' => null,
                ];
            }
            if (isset($tags['決定'])) {
                $cardTags[] = [
                    'card_id' => $loversId,
                    'tag_id' => $tags['決定']->id,
                    'position' => 'both', // 正逆位都適用
                    'is_default' => true,
                    'user_id' => null,
                ];
            }
            
            // 逆位標籤
            if (isset($tags['衝突'])) {
                $cardTags[] = [
                    'card_id' => $loversId,
                    'tag_id' => $tags['衝突']->id,
                    'position' => 'reversed',
                    'is_default' => true,
                    'user_id' => null,
                ];
            }
        }

        // ============================================
        // 範例 3: 太陽 (The Sun)
        // ============================================
        if (isset($cards['太陽'])) {
            $sunId = $cards['太陽']->id;
            
            // 正位標籤
            if (isset($tags['喜悅'])) {
                $cardTags[] = [
                    'card_id' => $sunId,
                    'tag_id' => $tags['喜悅']->id,
                    'position' => 'upright',
                    'is_default' => true,
                    'user_id' => null,
                ];
            }
            if (isset($tags['成功'])) {
                $cardTags[] = [
                    'card_id' => $sunId,
                    'tag_id' => $tags['成功']->id,
                    'position' => 'upright',
                    'is_default' => true,
                    'user_id' => null,
                ];
            }
            if (isset($tags['成就'])) {
                $cardTags[] = [
                    'card_id' => $sunId,
                    'tag_id' => $tags['成就']->id,
                    'position' => 'upright',
                    'is_default' => true,
                    'user_id' => null,
                ];
            }
        }

        // ============================================
        // 範例 4: 寶劍三 (Three of Swords)
        // ============================================
        if (isset($cards['寶劍三'])) {
            $threeSwordsId = $cards['寶劍三']->id;
            
            // 正位標籤
            if (isset($tags['悲傷'])) {
                $cardTags[] = [
                    'card_id' => $threeSwordsId,
                    'tag_id' => $tags['悲傷']->id,
                    'position' => 'upright',
                    'is_default' => true,
                    'user_id' => null,
                ];
            }
            if (isset($tags['失落'])) {
                $cardTags[] = [
                    'card_id' => $threeSwordsId,
                    'tag_id' => $tags['失落']->id,
                    'position' => 'upright',
                    'is_default' => true,
                    'user_id' => null,
                ];
            }
            if (isset($tags['背叛'])) {
                $cardTags[] = [
                    'card_id' => $threeSwordsId,
                    'tag_id' => $tags['背叛']->id,
                    'position' => 'upright',
                    'is_default' => true,
                    'user_id' => null,
                ];
            }
            
            // 逆位標籤
            if (isset($tags['療癒'])) {
                $cardTags[] = [
                    'card_id' => $threeSwordsId,
                    'tag_id' => $tags['療癒']->id,
                    'position' => 'reversed',
                    'is_default' => true,
                    'user_id' => null,
                ];
            }
        }

        // ============================================
        // 範例 5: 聖杯十 (Ten of Cups)
        // ============================================
        if (isset($cards['聖杯十'])) {
            $tenCupsId = $cards['聖杯十']->id;
            
            // 正位標籤
            if (isset($tags['快樂'])) {
                $cardTags[] = [
                    'card_id' => $tenCupsId,
                    'tag_id' => $tags['快樂']->id,
                    'position' => 'upright',
                    'is_default' => true,
                    'user_id' => null,
                ];
            }
            if (isset($tags['家人'])) {
                $cardTags[] = [
                    'card_id' => $tenCupsId,
                    'tag_id' => $tags['家人']->id,
                    'position' => 'upright',
                    'is_default' => true,
                    'user_id' => null,
                ];
            }
            if (isset($tags['親情'])) {
                $cardTags[] = [
                    'card_id' => $tenCupsId,
                    'tag_id' => $tags['親情']->id,
                    'position' => 'upright',
                    'is_default' => true,
                    'user_id' => null,
                ];
            }
        }

        // ============================================
        // 範例 6: 權杖王牌 (Ace of Wands)
        // ============================================
        if (isset($cards['權杖王牌'])) {
            $aceWandsId = $cards['權杖王牌']->id;
            
            // 正位標籤
            if (isset($tags['新開始'])) {
                $cardTags[] = [
                    'card_id' => $aceWandsId,
                    'tag_id' => $tags['新開始']->id,
                    'position' => 'upright',
                    'is_default' => true,
                    'user_id' => null,
                ];
            }
            if (isset($tags['創意'])) {
                $cardTags[] = [
                    'card_id' => $aceWandsId,
                    'tag_id' => $tags['創意']->id,
                    'position' => 'upright',
                    'is_default' => true,
                    'user_id' => null,
                ];
            }
            if (isset($tags['機會'])) {
                $cardTags[] = [
                    'card_id' => $aceWandsId,
                    'tag_id' => $tags['機會']->id,
                    'position' => 'upright',
                    'is_default' => true,
                    'user_id' => null,
                ];
            }
        }

        // 批量插入
        if (!empty($cardTags)) {
            DB::table('card_tags')->insert($cardTags);
            $this->command->info('✅ 已為 ' . count($cardTags) . ' 個牌卡-標籤關聯建立範例資料');
        }

        $this->command->info('');
        $this->command->info('💡 提示：這只是範例資料，實際應用中需要為所有 78 張牌配置完整的標籤');
        $this->command->info('   您可以根據每張牌的牌義，為其配置適合的標籤');
    }
}
