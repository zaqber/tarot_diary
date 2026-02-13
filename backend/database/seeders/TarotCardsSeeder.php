<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TarotCardsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 獲取花色 ID
        $suits = DB::table('suits')->get()->keyBy('name_zh');

        $cards = [];

        // ============================================
        // 大阿爾克那 (Major Arcana) - 22 張
        // ============================================

        $majorArcana = [
            ['number' => 0, 'name' => 'The Fool', 'name_zh' => '愚者', 'official_meaning_upright' => '代表新的開始、冒險精神和無限的可能性。保持開放的心態迎接未知。', 'official_meaning_reversed' => '可能過於魯莽或不考慮後果。需要更謹慎地評估風險。'],
            ['number' => 1, 'name' => 'The Magician', 'name_zh' => '魔術師', 'official_meaning_upright' => '擁有實現目標的所有資源和能力。是時候將想法付諸實踐。', 'official_meaning_reversed' => '可能濫用才能或缺乏行動力。需要誠實面對自己的能力。'],
            ['number' => 2, 'name' => 'The High Priestess', 'name_zh' => '女祭司', 'official_meaning_upright' => '傾聽內心的聲音和直覺。答案可能來自於內在而非外在。', 'official_meaning_reversed' => '可能忽視直覺或過度依賴他人意見。需要重新連結內在智慧。'],
            ['number' => 3, 'name' => 'The Empress', 'name_zh' => '皇后', 'official_meaning_upright' => '代表豐盛、創造力和滋養。是收穫和成長的時期。', 'official_meaning_reversed' => '可能過度保護或忽視自我照顧。需要平衡給予與接受。'],
            ['number' => 4, 'name' => 'The Emperor', 'name_zh' => '皇帝', 'official_meaning_upright' => '建立秩序和結構。運用領導力和邏輯思維來達成目標。', 'official_meaning_reversed' => '可能過於僵化或控制欲強。需要在權威與彈性間取得平衡。'],
            ['number' => 5, 'name' => 'The Hierophant', 'name_zh' => '教皇', 'official_meaning_upright' => '尊重傳統和既定規則。尋求智慧導師的指引。', 'official_meaning_reversed' => '可能需要挑戰傳統或找到自己的道路。不要盲目跟從。'],
            ['number' => 6, 'name' => 'The Lovers', 'name_zh' => '戀人', 'official_meaning_upright' => '深刻的連結和重要的選擇。追隨內心做出真實的決定。', 'official_meaning_reversed' => '關係中的不和諧或價值觀衝突。需要重新審視選擇。'],
            ['number' => 7, 'name' => 'The Chariot', 'name_zh' => '戰車', 'official_meaning_upright' => '憑藉決心和意志力克服障礙。保持專注朝目標前進。', 'official_meaning_reversed' => '可能失去方向或內在衝突。需要重新找回掌控感。'],
            ['number' => 8, 'name' => 'Strength', 'name_zh' => '力量', 'official_meaning_upright' => '以溫和與堅定面對挑戰。真正的力量來自內心的勇氣。', 'official_meaning_reversed' => '可能缺乏自信或過於強硬。需要找到溫柔中的力量。'],
            ['number' => 9, 'name' => 'The Hermit', 'name_zh' => '隱者', 'official_meaning_upright' => '需要獨處和內省的時刻。從內在尋找答案和智慧。', 'official_meaning_reversed' => '可能過度孤立或逃避現實。需要平衡獨處與社交。'],
            ['number' => 10, 'name' => 'Wheel of Fortune', 'name_zh' => '命運之輪', 'official_meaning_upright' => '生命的循環和轉變。接受變化並順應自然的節奏。', 'official_meaning_reversed' => '可能抗拒必要的改變。需要接受生命的起伏。'],
            ['number' => 11, 'name' => 'Justice', 'name_zh' => '正義', 'official_meaning_upright' => '尋求真相和公平。做出符合道德的決定並承擔責任。', 'official_meaning_reversed' => '可能面對不公或逃避後果。需要誠實面對真相。'],
            ['number' => 12, 'name' => 'The Hanged Man', 'name_zh' => '倒吊者', 'official_meaning_upright' => '暫停和從新角度看事物。有時放手才能獲得更多。', 'official_meaning_reversed' => '可能過度犧牲或拖延決定。需要停止無謂的等待。'],
            ['number' => 13, 'name' => 'Death', 'name_zh' => '死神', 'official_meaning_upright' => '舊階段的結束帶來新的開始。擁抱轉變和成長。', 'official_meaning_reversed' => '可能抗拒必要的結束或改變。需要學會放手。'],
            ['number' => 14, 'name' => 'Temperance', 'name_zh' => '節制', 'official_meaning_upright' => '尋找中庸之道和內在平衡。耐心地融合不同元素。', 'official_meaning_reversed' => '生活可能失衡或過度。需要重新找回和諧。'],
            ['number' => 15, 'name' => 'The Devil', 'name_zh' => '惡魔', 'official_meaning_upright' => '意識到自我設限和不健康的依附。是時候面對陰影。', 'official_meaning_reversed' => '正在掙脫束縛或克服誘惑。邁向自由之路。'],
            ['number' => 16, 'name' => 'The Tower', 'name_zh' => '高塔', 'official_meaning_upright' => '突如其來的改變打破舊結構。雖然痛苦但帶來真相。', 'official_meaning_reversed' => '可能逃避必要的崩解。改變雖延遲但終將到來。'],
            ['number' => 17, 'name' => 'The Star', 'name_zh' => '星星', 'official_meaning_upright' => '重燃希望和信念。保持樂觀並相信美好的未來。', 'official_meaning_reversed' => '可能失去信心或感到迷失。需要重新找回內在之光。'],
            ['number' => 18, 'name' => 'The Moon', 'name_zh' => '月亮', 'official_meaning_upright' => '面對內心的恐懼和幻象。相信直覺穿越迷霧。', 'official_meaning_reversed' => '恐懼正在消散或真相顯現。更加清晰地看待情況。'],
            ['number' => 19, 'name' => 'The Sun', 'name_zh' => '太陽', 'official_meaning_upright' => '充滿喜悅和成功的時期。盡情享受生命的美好。', 'official_meaning_reversed' => '快樂可能暫時被遮蔽。需要找回內在的光芒。'],
            ['number' => 20, 'name' => 'Judgement', 'name_zh' => '審判', 'official_meaning_upright' => '重要的覺醒和反思時刻。是時候做出關鍵決定。', 'official_meaning_reversed' => '可能逃避評判或內疚。需要寬恕並向前邁進。'],
            ['number' => 21, 'name' => 'The World', 'name_zh' => '世界', 'official_meaning_upright' => '達成重要里程碑和圓滿結局。準備迎接新的循環。', 'official_meaning_reversed' => '某些事未完成或缺少閉合感。需要完成最後一步。'],
        ];

        foreach ($majorArcana as $card) {
            $cards[] = array_merge($card, [
                'card_type' => 'major',
                'suit_id' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // ============================================
        // 小阿爾克那 (Minor Arcana) - 56 張
        // ============================================

        // 權杖牌組
        $wandsCards = [
            ['number' => 1, 'name' => 'Ace of Wands', 'name_zh' => '權杖王牌'],
            ['number' => 2, 'name' => 'Two of Wands', 'name_zh' => '權杖二'],
            ['number' => 3, 'name' => 'Three of Wands', 'name_zh' => '權杖三'],
            ['number' => 4, 'name' => 'Four of Wands', 'name_zh' => '權杖四'],
            ['number' => 5, 'name' => 'Five of Wands', 'name_zh' => '權杖五'],
            ['number' => 6, 'name' => 'Six of Wands', 'name_zh' => '權杖六'],
            ['number' => 7, 'name' => 'Seven of Wands', 'name_zh' => '權杖七'],
            ['number' => 8, 'name' => 'Eight of Wands', 'name_zh' => '權杖八'],
            ['number' => 9, 'name' => 'Nine of Wands', 'name_zh' => '權杖九'],
            ['number' => 10, 'name' => 'Ten of Wands', 'name_zh' => '權杖十'],
            ['number' => 11, 'name' => 'Page of Wands', 'name_zh' => '權杖侍者'],
            ['number' => 12, 'name' => 'Knight of Wands', 'name_zh' => '權杖騎士'],
            ['number' => 13, 'name' => 'Queen of Wands', 'name_zh' => '權杖王后'],
            ['number' => 14, 'name' => 'King of Wands', 'name_zh' => '權杖國王'],
        ];

        foreach ($wandsCards as $card) {
            $cards[] = array_merge($card, [
                'card_type' => 'minor',
                'suit_id' => $suits['權杖']->id,
                'official_meaning_upright' => '',
                'official_meaning_reversed' => '',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // 聖杯牌組
        $cupsCards = [
            ['number' => 1, 'name' => 'Ace of Cups', 'name_zh' => '聖杯王牌'],
            ['number' => 2, 'name' => 'Two of Cups', 'name_zh' => '聖杯二'],
            ['number' => 3, 'name' => 'Three of Cups', 'name_zh' => '聖杯三'],
            ['number' => 4, 'name' => 'Four of Cups', 'name_zh' => '聖杯四'],
            ['number' => 5, 'name' => 'Five of Cups', 'name_zh' => '聖杯五'],
            ['number' => 6, 'name' => 'Six of Cups', 'name_zh' => '聖杯六'],
            ['number' => 7, 'name' => 'Seven of Cups', 'name_zh' => '聖杯七'],
            ['number' => 8, 'name' => 'Eight of Cups', 'name_zh' => '聖杯八'],
            ['number' => 9, 'name' => 'Nine of Cups', 'name_zh' => '聖杯九'],
            ['number' => 10, 'name' => 'Ten of Cups', 'name_zh' => '聖杯十'],
            ['number' => 11, 'name' => 'Page of Cups', 'name_zh' => '聖杯侍者'],
            ['number' => 12, 'name' => 'Knight of Cups', 'name_zh' => '聖杯騎士'],
            ['number' => 13, 'name' => 'Queen of Cups', 'name_zh' => '聖杯王后'],
            ['number' => 14, 'name' => 'King of Cups', 'name_zh' => '聖杯國王'],
        ];

        foreach ($cupsCards as $card) {
            $cards[] = array_merge($card, [
                'card_type' => 'minor',
                'suit_id' => $suits['聖杯']->id,
                'official_meaning_upright' => '',
                'official_meaning_reversed' => '',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // 寶劍牌組
        $swordsCards = [
            ['number' => 1, 'name' => 'Ace of Swords', 'name_zh' => '寶劍王牌'],
            ['number' => 2, 'name' => 'Two of Swords', 'name_zh' => '寶劍二'],
            ['number' => 3, 'name' => 'Three of Swords', 'name_zh' => '寶劍三'],
            ['number' => 4, 'name' => 'Four of Swords', 'name_zh' => '寶劍四'],
            ['number' => 5, 'name' => 'Five of Swords', 'name_zh' => '寶劍五'],
            ['number' => 6, 'name' => 'Six of Swords', 'name_zh' => '寶劍六'],
            ['number' => 7, 'name' => 'Seven of Swords', 'name_zh' => '寶劍七'],
            ['number' => 8, 'name' => 'Eight of Swords', 'name_zh' => '寶劍八'],
            ['number' => 9, 'name' => 'Nine of Swords', 'name_zh' => '寶劍九'],
            ['number' => 10, 'name' => 'Ten of Swords', 'name_zh' => '寶劍十'],
            ['number' => 11, 'name' => 'Page of Swords', 'name_zh' => '寶劍侍者'],
            ['number' => 12, 'name' => 'Knight of Swords', 'name_zh' => '寶劍騎士'],
            ['number' => 13, 'name' => 'Queen of Swords', 'name_zh' => '寶劍王后'],
            ['number' => 14, 'name' => 'King of Swords', 'name_zh' => '寶劍國王'],
        ];

        foreach ($swordsCards as $card) {
            $cards[] = array_merge($card, [
                'card_type' => 'minor',
                'suit_id' => $suits['寶劍']->id,
                'official_meaning_upright' => '',
                'official_meaning_reversed' => '',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // 錢幣牌組
        $pentaclesCards = [
            ['number' => 1, 'name' => 'Ace of Pentacles', 'name_zh' => '錢幣王牌'],
            ['number' => 2, 'name' => 'Two of Pentacles', 'name_zh' => '錢幣二'],
            ['number' => 3, 'name' => 'Three of Pentacles', 'name_zh' => '錢幣三'],
            ['number' => 4, 'name' => 'Four of Pentacles', 'name_zh' => '錢幣四'],
            ['number' => 5, 'name' => 'Five of Pentacles', 'name_zh' => '錢幣五'],
            ['number' => 6, 'name' => 'Six of Pentacles', 'name_zh' => '錢幣六'],
            ['number' => 7, 'name' => 'Seven of Pentacles', 'name_zh' => '錢幣七'],
            ['number' => 8, 'name' => 'Eight of Pentacles', 'name_zh' => '錢幣八'],
            ['number' => 9, 'name' => 'Nine of Pentacles', 'name_zh' => '錢幣九'],
            ['number' => 10, 'name' => 'Ten of Pentacles', 'name_zh' => '錢幣十'],
            ['number' => 11, 'name' => 'Page of Pentacles', 'name_zh' => '錢幣侍者'],
            ['number' => 12, 'name' => 'Knight of Pentacles', 'name_zh' => '錢幣騎士'],
            ['number' => 13, 'name' => 'Queen of Pentacles', 'name_zh' => '錢幣王后'],
            ['number' => 14, 'name' => 'King of Pentacles', 'name_zh' => '錢幣國王'],
        ];

        foreach ($pentaclesCards as $card) {
            $cards[] = array_merge($card, [
                'card_type' => 'minor',
                'suit_id' => $suits['錢幣']->id,
                'official_meaning_upright' => '',
                'official_meaning_reversed' => '',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // 批量插入所有卡片
        DB::table('tarot_cards')->insert($cards);
    }
}
