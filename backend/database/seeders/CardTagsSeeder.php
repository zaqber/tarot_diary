<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CardTagsSeeder extends Seeder
{
    /**
     * 每張牌對應的標籤（name_zh => [ [tag_name_zh, position], ... ]）
     * position: upright / reversed / both
     */
    private function getCardTagMapping(): array
    {
        return [
            // ========== 大阿爾克那 ==========
            '愚者'   => [['新開始', 'upright'], ['希望', 'upright'], ['變化', 'both'], ['焦慮', 'reversed'], ['挫折', 'reversed']],
            '魔術師'  => [['創意', 'upright'], ['行動', 'upright'], ['自信', 'upright'], ['困惑', 'reversed']],
            '女祭司'  => [['直覺', 'upright'], ['沉思', 'upright'], ['平靜', 'upright'], ['反思', 'both']],
            '皇后'   => [['成長', 'upright'], ['愛', 'upright'], ['豐盛', 'upright'], ['失衡', 'reversed']],
            '皇帝'   => [['領導', 'upright'], ['穩定', 'upright'], ['權威人物', 'upright'], ['緊張', 'reversed']],
            '教皇'   => [['導師', 'upright'], ['學習', 'upright'], ['反思', 'upright'], ['變化', 'reversed']],
            '戀人'   => [['愛', 'upright'], ['浪漫', 'upright'], ['決定', 'both'], ['衝突', 'reversed']],
            '戰車'   => [['行動', 'upright'], ['突破', 'upright'], ['自信', 'upright'], ['混亂', 'reversed']],
            '力量'   => [['自信', 'upright'], ['成長', 'upright'], ['平衡', 'upright'], ['恐懼', 'reversed']],
            '隱者'   => [['沉思', 'upright'], ['反思', 'upright'], ['孤獨', 'upright'], ['等待', 'reversed']],
            '命運之輪' => [['變化', 'both'], ['機會', 'upright'], ['轉化', 'both'], ['失落', 'reversed']],
            '正義'   => [['決定', 'upright'], ['平衡', 'upright'], ['反思', 'upright'], ['困惑', 'reversed']],
            '倒吊者'  => [['等待', 'upright'], ['反思', 'upright'], ['轉化', 'upright'], ['行動', 'reversed']],
            '死神'   => [['結束', 'upright'], ['轉化', 'upright'], ['新開始', 'upright'], ['恐懼', 'reversed']],
            '節制'   => [['平衡', 'upright'], ['平靜', 'upright'], ['療癒', 'upright'], ['失衡', 'reversed']],
            '惡魔'   => [['緊張', 'upright'], ['困惑', 'upright'], ['障礙', 'upright'], ['突破', 'reversed']],
            '高塔'   => [['變化', 'upright'], ['混亂', 'upright'], ['突破', 'upright'], ['恐懼', 'reversed']],
            '星星'   => [['希望', 'upright'], ['療癒', 'upright'], ['平靜', 'upright'], ['失落', 'reversed']],
            '月亮'   => [['恐懼', 'upright'], ['困惑', 'upright'], ['直覺', 'upright'], ['平靜', 'reversed']],
            '太陽'   => [['喜悅', 'upright'], ['成功', 'upright'], ['成就', 'upright'], ['悲傷', 'reversed']],
            '審判'   => [['反思', 'upright'], ['轉化', 'upright'], ['決定', 'upright'], ['後悔', 'reversed']],
            '世界'   => [['成就', 'upright'], ['完成', 'upright'], ['新開始', 'upright'], ['等待', 'reversed']],

            // ========== 權杖 ==========
            '權杖王牌' => [['新開始', 'upright'], ['創意', 'upright'], ['機會', 'upright']],
            '權杖二'  => [['決定', 'upright'], ['等待', 'upright'], ['機會', 'reversed']],
            '權杖三'  => [['旅行', 'upright'], ['機會', 'upright'], ['合作', 'upright']],
            '權杖四'  => [['慶祝', 'upright'], ['穩定', 'upright'], ['家人', 'upright']],
            '權杖五'  => [['衝突', 'upright'], ['挑戰', 'upright'], ['緊張', 'reversed']],
            '權杖六'  => [['成功', 'upright'], ['自信', 'upright'], ['成就', 'upright']],
            '權杖七'  => [['挑戰', 'upright'], ['行動', 'upright'], ['自信', 'reversed']],
            '權杖八'  => [['行動', 'upright'], ['變化', 'upright'], ['旅行', 'upright']],
            '權杖九'  => [['等待', 'upright'], ['挑戰', 'upright'], ['焦慮', 'reversed']],
            '權杖十'  => [['工作', 'upright'], ['挑戰', 'upright'], ['壓力', 'upright']],
            '權杖侍者' => [['創意', 'upright'], ['新開始', 'upright'], ['學習', 'upright']],
            '權杖騎士' => [['行動', 'upright'], ['變化', 'upright'], ['旅行', 'upright']],
            '權杖王后' => [['自信', 'upright'], ['領導', 'upright'], ['熱情', 'upright']],
            '權杖國王' => [['領導', 'upright'], ['事業', 'upright'], ['穩定', 'upright']],

            // ========== 聖杯 ==========
            '聖杯王牌' => [['愛', 'upright'], ['新開始', 'upright'], ['連結', 'upright']],
            '聖杯二'  => [['浪漫', 'upright'], ['合作', 'upright'], ['連結', 'upright']],
            '聖杯三'  => [['慶祝', 'upright'], ['朋友', 'upright'], ['喜悅', 'upright']],
            '聖杯四'  => [['沉思', 'upright'], ['等待', 'upright'], ['冷漠', 'upright']],
            '聖杯五'  => [['悲傷', 'upright'], ['失落', 'upright'], ['療癒', 'reversed']],
            '聖杯六'  => [['家人', 'upright'], ['親情', 'upright'], ['平靜', 'upright']],
            '聖杯七'  => [['困惑', 'upright'], ['選擇', 'upright'], ['幻想', 'upright']],
            '聖杯八'  => [['變化', 'upright'], ['結束', 'upright'], ['追尋', 'upright']],
            '聖杯九'  => [['喜悅', 'upright'], ['成就', 'upright'], ['滿足', 'upright']],
            '聖杯十'  => [['快樂', 'upright'], ['家人', 'upright'], ['親情', 'upright']],
            '聖杯侍者' => [['直覺', 'upright'], ['創意', 'upright'], ['學習', 'upright']],
            '聖杯騎士' => [['浪漫', 'upright'], ['感性', 'upright'], ['追求', 'upright']],
            '聖杯王后' => [['愛', 'upright'], ['直覺', 'upright'], ['療癒', 'upright']],
            '聖杯國王' => [['領導', 'upright'], ['平靜', 'upright'], ['情感成熟', 'upright']],

            // ========== 寶劍 ==========
            '寶劍王牌' => [['突破', 'upright'], ['決定', 'upright'], ['真相', 'upright']],
            '寶劍二'  => [['決定', 'upright'], ['等待', 'upright'], ['困惑', 'upright']],
            '寶劍三'  => [['悲傷', 'upright'], ['失落', 'upright'], ['背叛', 'upright'], ['療癒', 'reversed']],
            '寶劍四'  => [['等待', 'upright'], ['療癒', 'upright'], ['反思', 'upright']],
            '寶劍五'  => [['衝突', 'upright'], ['緊張', 'upright'], ['和解', 'reversed']],
            '寶劍六'  => [['過渡', 'upright'], ['療癒', 'upright'], ['旅行', 'upright']],
            '寶劍七'  => [['策略', 'upright'], ['逃避', 'upright'], ['真相', 'reversed']],
            '寶劍八'  => [['恐懼', 'upright'], ['障礙', 'upright'], ['突破', 'reversed']],
            '寶劍九'  => [['焦慮', 'upright'], ['恐懼', 'upright'], ['悲傷', 'upright']],
            '寶劍十'  => [['結束', 'upright'], ['失落', 'upright'], ['新開始', 'reversed']],
            '寶劍侍者' => [['學習', 'upright'], ['好奇', 'upright'], ['訊息', 'upright']],
            '寶劍騎士' => [['行動', 'upright'], ['衝動', 'upright'], ['決定', 'upright']],
            '寶劍王后' => [['獨立', 'upright'], ['清晰', 'upright'], ['冷靜', 'upright']],
            '寶劍國王' => [['領導', 'upright'], ['權威人物', 'upright'], ['決定', 'upright']],

            // ========== 錢幣 ==========
            '錢幣王牌' => [['新開始', 'upright'], ['機會', 'upright'], ['穩定', 'upright']],
            '錢幣二'  => [['平衡', 'upright'], ['變化', 'upright'], ['失衡', 'reversed']],
            '錢幣三'  => [['團隊合作', 'upright'], ['專案', 'upright'], ['合作', 'upright']],
            '錢幣四'  => [['穩定', 'upright'], ['安全感', 'upright'], ['恐懼', 'reversed']],
            '錢幣五'  => [['失落', 'upright'], ['孤獨', 'upright'], ['療癒', 'reversed']],
            '錢幣六'  => [['合作', 'upright'], ['感恩', 'upright'], ['平衡', 'upright']],
            '錢幣七'  => [['等待', 'upright'], ['反思', 'upright'], ['成長', 'upright']],
            '錢幣八'  => [['工作', 'upright'], ['學習', 'upright'], ['成就', 'upright']],
            '錢幣九'  => [['成功', 'upright'], ['穩定', 'upright'], ['自信', 'upright']],
            '錢幣十'  => [['家人', 'upright'], ['穩定', 'upright'], ['成就', 'upright']],
            '錢幣侍者' => [['學習', 'upright'], ['機會', 'upright'], ['新開始', 'upright']],
            '錢幣騎士' => [['穩定', 'upright'], ['工作', 'upright'], ['等待', 'upright']],
            '錢幣王后' => [['穩定', 'upright'], ['成長', 'upright'], ['家人', 'upright']],
            '錢幣國王' => [['領導', 'upright'], ['事業', 'upright'], ['穩定', 'upright']],
        ];
    }

    /**
     * 標籤名稱對照：映射中使用的名稱 -> TagsSeeder 內實際的 name_zh
     * （若無對應則該標籤會略過）
     */
    private function getTagNameAliases(): array
    {
        return [
            '直覺' => '沉思',      // 無「直覺」則用沉思
            '豐盛' => '成長',
            '完成' => '成就',
            '壓力' => '挑戰',
            '熱情' => '創意',
            '冷漠' => '中性',
            '選擇' => '決定',
            '幻想' => '困惑',
            '追尋' => '變化',
            '滿足' => '喜悅',
            '感性' => '浪漫',
            '追求' => '浪漫',
            '情感成熟' => '平靜',
            '真相' => '反思',
            '策略' => '決定',
            '逃避' => '恐懼',
            '好奇' => '學習',
            '訊息' => '學習',
            '衝動' => '行動',
            '獨立' => '自信',
            '清晰' => '冷靜',
            '安全感' => '穩定',
        ];
    }

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $cards = DB::table('tarot_cards')->get()->keyBy('name_zh');
        $tags = DB::table('tags')->get()->keyBy('name_zh');
        $mapping = $this->getCardTagMapping();
        $aliases = $this->getTagNameAliases();

        $cardTags = [];
        $missingTags = [];
        $seen = [];

        foreach ($cards as $card) {
            $nameZh = $card->name_zh;
            $pairs = $mapping[$nameZh] ?? null;

            if ($pairs === null) {
                $pairs = [['反思', 'both'], ['變化', 'both']];
            }

            foreach ($pairs as [$tagNameZh, $position]) {
                $resolvedName = $aliases[$tagNameZh] ?? $tagNameZh;
                $tag = $tags->get($resolvedName);
                if (!$tag) {
                    $missingTags[$tagNameZh] = true;
                    continue;
                }
                $key = $card->id . '_' . $tag->id . '_' . $position;
                if (isset($seen[$key])) {
                    continue;
                }
                $seen[$key] = true;
                $cardTags[] = [
                    'card_id' => $card->id,
                    'tag_id' => $tag->id,
                    'position' => $position,
                    'is_default' => true,
                    'user_id' => null,
                ];
            }
        }

        $missingTags = array_keys($missingTags);
        if (!empty($missingTags)) {
            $this->command->warn('以下標籤在 tags 表中不存在，已略過：' . implode('、', $missingTags));
        }

        // 先刪除既有預設關聯，再寫入（避免重複執行時重複資料）
        DB::table('card_tags')->where('is_default', true)->whereNull('user_id')->delete();

        if (!empty($cardTags)) {
            DB::table('card_tags')->insert($cardTags);
            $this->command->info('已為全部 ' . $cards->count() . ' 張塔羅牌建立共 ' . count($cardTags) . ' 筆標籤關聯。');
        }
    }
}
