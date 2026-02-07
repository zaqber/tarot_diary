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
            ['number' => 0, 'name' => 'The Fool', 'name_zh' => '愚者', 'keywords_upright' => '新開始,冒險,天真,自由', 'keywords_reversed' => '魯莽,不負責任,風險', 'official_meaning_upright' => '代表新的開始、冒險精神和無限的可能性。保持開放的心態迎接未知。', 'official_meaning_reversed' => '可能過於魯莽或不考慮後果。需要更謹慎地評估風險。'],
            ['number' => 1, 'name' => 'The Magician', 'name_zh' => '魔術師', 'keywords_upright' => '創造力,技能,行動力,顯化', 'keywords_reversed' => '操縱,詭計,缺乏行動', 'official_meaning_upright' => '擁有實現目標的所有資源和能力。是時候將想法付諸實踐。', 'official_meaning_reversed' => '可能濫用才能或缺乏行動力。需要誠實面對自己的能力。'],
            ['number' => 2, 'name' => 'The High Priestess', 'name_zh' => '女祭司', 'keywords_upright' => '直覺,神秘,內在智慧,潛意識', 'keywords_reversed' => '隱藏秘密,缺乏洞察,迷惑', 'official_meaning_upright' => '傾聽內心的聲音和直覺。答案可能來自於內在而非外在。', 'official_meaning_reversed' => '可能忽視直覺或過度依賴他人意見。需要重新連結內在智慧。'],
            ['number' => 3, 'name' => 'The Empress', 'name_zh' => '皇后', 'keywords_upright' => '豐饒,母性,創造,自然', 'keywords_reversed' => '依賴,窒息,缺乏成長', 'official_meaning_upright' => '代表豐盛、創造力和滋養。是收穫和成長的時期。', 'official_meaning_reversed' => '可能過度保護或忽視自我照顧。需要平衡給予與接受。'],
            ['number' => 4, 'name' => 'The Emperor', 'name_zh' => '皇帝', 'keywords_upright' => '權威,結構,領導,穩定', 'keywords_reversed' => '專制,控制過度,缺乏紀律', 'official_meaning_upright' => '建立秩序和結構。運用領導力和邏輯思維來達成目標。', 'official_meaning_reversed' => '可能過於僵化或控制欲強。需要在權威與彈性間取得平衡。'],
            ['number' => 5, 'name' => 'The Hierophant', 'name_zh' => '教皇', 'keywords_upright' => '傳統,教育,信仰,導師', 'keywords_reversed' => '叛逆,非傳統,質疑規則', 'official_meaning_upright' => '尊重傳統和既定規則。尋求智慧導師的指引。', 'official_meaning_reversed' => '可能需要挑戰傳統或找到自己的道路。不要盲目跟從。'],
            ['number' => 6, 'name' => 'The Lovers', 'name_zh' => '戀人', 'keywords_upright' => '愛情,和諧,選擇,關係', 'keywords_reversed' => '不和諧,錯誤選擇,失衡', 'official_meaning_upright' => '深刻的連結和重要的選擇。追隨內心做出真實的決定。', 'official_meaning_reversed' => '關係中的不和諧或價值觀衝突。需要重新審視選擇。'],
            ['number' => 7, 'name' => 'The Chariot', 'name_zh' => '戰車', 'keywords_upright' => '勝利,意志力,掌控,前進', 'keywords_reversed' => '失控,缺乏方向,挫敗', 'official_meaning_upright' => '憑藉決心和意志力克服障礙。保持專注朝目標前進。', 'official_meaning_reversed' => '可能失去方向或內在衝突。需要重新找回掌控感。'],
            ['number' => 8, 'name' => 'Strength', 'name_zh' => '力量', 'keywords_upright' => '勇氣,耐心,慈悲,內在力量', 'keywords_reversed' => '自我懷疑,脆弱,缺乏信心', 'official_meaning_upright' => '以溫和與堅定面對挑戰。真正的力量來自內心的勇氣。', 'official_meaning_reversed' => '可能缺乏自信或過於強硬。需要找到溫柔中的力量。'],
            ['number' => 9, 'name' => 'The Hermit', 'name_zh' => '隱者', 'keywords_upright' => '內省,孤獨,尋求真理,智慧', 'keywords_reversed' => '孤立,孤獨,逃避', 'official_meaning_upright' => '需要獨處和內省的時刻。從內在尋找答案和智慧。', 'official_meaning_reversed' => '可能過度孤立或逃避現實。需要平衡獨處與社交。'],
            ['number' => 10, 'name' => 'Wheel of Fortune', 'name_zh' => '命運之輪', 'keywords_upright' => '變化,循環,命運,轉機', 'keywords_reversed' => '壞運氣,抗拒改變,失控', 'official_meaning_upright' => '生命的循環和轉變。接受變化並順應自然的節奏。', 'official_meaning_reversed' => '可能抗拒必要的改變。需要接受生命的起伏。'],
            ['number' => 11, 'name' => 'Justice', 'name_zh' => '正義', 'keywords_upright' => '公平,真相,法律,平衡', 'keywords_reversed' => '不公正,偏見,逃避責任', 'official_meaning_upright' => '尋求真相和公平。做出符合道德的決定並承擔責任。', 'official_meaning_reversed' => '可能面對不公或逃避後果。需要誠實面對真相。'],
            ['number' => 12, 'name' => 'The Hanged Man', 'name_zh' => '倒吊者', 'keywords_upright' => '放手,新視角,犧牲,等待', 'keywords_reversed' => '拖延,抗拒,無謂犧牲', 'official_meaning_upright' => '暫停和從新角度看事物。有時放手才能獲得更多。', 'official_meaning_reversed' => '可能過度犧牲或拖延決定。需要停止無謂的等待。'],
            ['number' => 13, 'name' => 'Death', 'name_zh' => '死神', 'keywords_upright' => '結束,轉變,重生,放下', 'keywords_reversed' => '抗拒改變,停滯,無法放手', 'official_meaning_upright' => '舊階段的結束帶來新的開始。擁抱轉變和成長。', 'official_meaning_reversed' => '可能抗拒必要的結束或改變。需要學會放手。'],
            ['number' => 14, 'name' => 'Temperance', 'name_zh' => '節制', 'keywords_upright' => '平衡,耐心,和諧,節制', 'keywords_reversed' => '不平衡,過度,缺乏和諧', 'official_meaning_upright' => '尋找中庸之道和內在平衡。耐心地融合不同元素。', 'official_meaning_reversed' => '生活可能失衡或過度。需要重新找回和諧。'],
            ['number' => 15, 'name' => 'The Devil', 'name_zh' => '惡魔', 'keywords_upright' => '束縛,誘惑,物質主義,成癮', 'keywords_reversed' => '釋放,覺醒,擺脫束縛', 'official_meaning_upright' => '意識到自我設限和不健康的依附。是時候面對陰影。', 'official_meaning_reversed' => '正在掙脫束縛或克服誘惑。邁向自由之路。'],
            ['number' => 16, 'name' => 'The Tower', 'name_zh' => '高塔', 'keywords_upright' => '突變,混亂,啟示,崩潰', 'keywords_reversed' => '逃避災難,恐懼改變,延遲', 'official_meaning_upright' => '突如其來的改變打破舊結構。雖然痛苦但帶來真相。', 'official_meaning_reversed' => '可能逃避必要的崩解。改變雖延遲但終將到來。'],
            ['number' => 17, 'name' => 'The Star', 'name_zh' => '星星', 'keywords_upright' => '希望,靈感,寧靜,療癒', 'keywords_reversed' => '失去信心,絕望,缺乏動力', 'official_meaning_upright' => '重燃希望和信念。保持樂觀並相信美好的未來。', 'official_meaning_reversed' => '可能失去信心或感到迷失。需要重新找回內在之光。'],
            ['number' => 18, 'name' => 'The Moon', 'name_zh' => '月亮', 'keywords_upright' => '幻象,直覺,潛意識,恐懼', 'keywords_reversed' => '釋放恐懼,真相揭露,清晰', 'official_meaning_upright' => '面對內心的恐懼和幻象。相信直覺穿越迷霧。', 'official_meaning_reversed' => '恐懼正在消散或真相顯現。更加清晰地看待情況。'],
            ['number' => 19, 'name' => 'The Sun', 'name_zh' => '太陽', 'keywords_upright' => '成功,喜悅,活力,真實', 'keywords_reversed' => '過度樂觀,延遲快樂,負面', 'official_meaning_upright' => '充滿喜悅和成功的時期。盡情享受生命的美好。', 'official_meaning_reversed' => '快樂可能暫時被遮蔽。需要找回內在的光芒。'],
            ['number' => 20, 'name' => 'Judgement', 'name_zh' => '審判', 'keywords_upright' => '重生,反思,覺醒,決定', 'keywords_reversed' => '自我懷疑,未完結,逃避責任', 'official_meaning_upright' => '重要的覺醒和反思時刻。是時候做出關鍵決定。', 'official_meaning_reversed' => '可能逃避評判或內疚。需要寬恕並向前邁進。'],
            ['number' => 21, 'name' => 'The World', 'name_zh' => '世界', 'keywords_upright' => '完成,成就,整合,圓滿', 'keywords_reversed' => '未完成,缺乏閉合,尋求閉環', 'official_meaning_upright' => '達成重要里程碑和圓滿結局。準備迎接新的循環。', 'official_meaning_reversed' => '某些事未完成或缺少閉合感。需要完成最後一步。'],
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
            ['number' => 1, 'name' => 'Ace of Wands', 'name_zh' => '權杖王牌', 'keywords_upright' => '靈感,新機會,創意,潛力', 'keywords_reversed' => '缺乏方向,延遲,錯失機會'],
            ['number' => 2, 'name' => 'Two of Wands', 'name_zh' => '權杖二', 'keywords_upright' => '計劃,決策,未來願景,進展', 'keywords_reversed' => '恐懼未知,缺乏計劃,猶豫不決'],
            ['number' => 3, 'name' => 'Three of Wands', 'name_zh' => '權杖三', 'keywords_upright' => '擴展,遠見,機會,成長', 'keywords_reversed' => '計劃受阻,缺乏遠見,延遲'],
            ['number' => 4, 'name' => 'Four of Wands', 'name_zh' => '權杖四', 'keywords_upright' => '慶祝,和諧,婚禮,穩定', 'keywords_reversed' => '不穩定,缺乏支持,延遲慶祝'],
            ['number' => 5, 'name' => 'Five of Wands', 'name_zh' => '權杖五', 'keywords_upright' => '競爭,衝突,緊張,挑戰', 'keywords_reversed' => '避免衝突,和解,內在衝突'],
            ['number' => 6, 'name' => 'Six of Wands', 'name_zh' => '權杖六', 'keywords_upright' => '勝利,認可,成功,驕傲', 'keywords_reversed' => '自負,缺乏認可,失敗'],
            ['number' => 7, 'name' => 'Seven of Wands', 'name_zh' => '權杖七', 'keywords_upright' => '挑戰,堅持,防衛,毅力', 'keywords_reversed' => '不知所措,放棄,疲憊'],
            ['number' => 8, 'name' => 'Eight of Wands', 'name_zh' => '權杖八', 'keywords_upright' => '速度,行動,快速變化,旅行', 'keywords_reversed' => '延遲,挫折,等待'],
            ['number' => 9, 'name' => 'Nine of Wands', 'name_zh' => '權杖九', 'keywords_upright' => '韌性,勇氣,堅持,防備', 'keywords_reversed' => '偏執,固執,疲憊'],
            ['number' => 10, 'name' => 'Ten of Wands', 'name_zh' => '權杖十', 'keywords_upright' => '負擔,責任,壓力,努力', 'keywords_reversed' => '卸下重擔,委派,倦怠'],
            ['number' => 11, 'name' => 'Page of Wands', 'name_zh' => '權杖侍者', 'keywords_upright' => '探索,熱情,自由精神,好消息', 'keywords_reversed' => '缺乏方向,拖延,壞消息'],
            ['number' => 12, 'name' => 'Knight of Wands', 'name_zh' => '權杖騎士', 'keywords_upright' => '能量,衝動,冒險,熱情', 'keywords_reversed' => '魯莽,不耐煩,衝動'],
            ['number' => 13, 'name' => 'Queen of Wands', 'name_zh' => '權杖王后', 'keywords_upright' => '自信,熱情,獨立,魅力', 'keywords_reversed' => '自私,嫉妒,不安全感'],
            ['number' => 14, 'name' => 'King of Wands', 'name_zh' => '權杖國王', 'keywords_upright' => '領導,願景,企業家精神,榮譽', 'keywords_reversed' => '專橫,衝動,殘酷'],
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
            ['number' => 1, 'name' => 'Ace of Cups', 'name_zh' => '聖杯王牌', 'keywords_upright' => '新戀情,直覺,創意,愛', 'keywords_reversed' => '情感封閉,壓抑情感,冷漠'],
            ['number' => 2, 'name' => 'Two of Cups', 'name_zh' => '聖杯二', 'keywords_upright' => '伴侶關係,愛情,和諧,連結', 'keywords_reversed' => '不平衡,緊張,分離'],
            ['number' => 3, 'name' => 'Three of Cups', 'name_zh' => '聖杯三', 'keywords_upright' => '友誼,慶祝,社交,喜悅', 'keywords_reversed' => '獨立,過度放縱,三角關係'],
            ['number' => 4, 'name' => 'Four of Cups', 'name_zh' => '聖杯四', 'keywords_upright' => '冥想,沉思,無聊,不滿', 'keywords_reversed' => '動機,重新評估,新視角'],
            ['number' => 5, 'name' => 'Five of Cups', 'name_zh' => '聖杯五', 'keywords_upright' => '失望,悲傷,後悔,失落', 'keywords_reversed' => '接受,前進,寬恕'],
            ['number' => 6, 'name' => 'Six of Cups', 'name_zh' => '聖杯六', 'keywords_upright' => '懷舊,童年,天真,喜悅', 'keywords_reversed' => '活在過去,不成熟,困在過去'],
            ['number' => 7, 'name' => 'Seven of Cups', 'name_zh' => '聖杯七', 'keywords_upright' => '選擇,幻想,幻覺,願望', 'keywords_reversed' => '清晰,決定,專注'],
            ['number' => 8, 'name' => 'Eight of Cups', 'name_zh' => '聖杯八', 'keywords_upright' => '離開,撤退,尋求真理,失望', 'keywords_reversed' => '恐懼改變,停滯,逃避'],
            ['number' => 9, 'name' => 'Nine of Cups', 'name_zh' => '聖杯九', 'keywords_upright' => '滿足,願望成真,喜悅,豐盛', 'keywords_reversed' => '貪婪,不滿,物質主義'],
            ['number' => 10, 'name' => 'Ten of Cups', 'name_zh' => '聖杯十', 'keywords_upright' => '家庭和諧,幸福,情感圓滿,喜悅', 'keywords_reversed' => '家庭問題,分裂,價值觀衝突'],
            ['number' => 11, 'name' => 'Page of Cups', 'name_zh' => '聖杯侍者', 'keywords_upright' => '創意,直覺,好消息,敏感', 'keywords_reversed' => '情緒不成熟,幼稚,壞消息'],
            ['number' => 12, 'name' => 'Knight of Cups', 'name_zh' => '聖杯騎士', 'keywords_upright' => '浪漫,魅力,想像力,理想主義', 'keywords_reversed' => '不切實際,情緒化,失望'],
            ['number' => 13, 'name' => 'Queen of Cups', 'name_zh' => '聖杯王后', 'keywords_upright' => '慈悲,直覺,溫暖,支持', 'keywords_reversed' => '情緒不穩,依賴,不安全感'],
            ['number' => 14, 'name' => 'King of Cups', 'name_zh' => '聖杯國王', 'keywords_upright' => '情緒平衡,外交,慈悲,智慧', 'keywords_reversed' => '情緒操縱,波動,冷漠'],
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
            ['number' => 1, 'name' => 'Ace of Swords', 'name_zh' => '寶劍王牌', 'keywords_upright' => '突破,清晰,真相,新想法', 'keywords_reversed' => '混亂,殘酷,誤解'],
            ['number' => 2, 'name' => 'Two of Swords', 'name_zh' => '寶劍二', 'keywords_upright' => '困難決擇,僵局,平衡,迴避', 'keywords_reversed' => '優柔寡斷,信息釋放,解決'],
            ['number' => 3, 'name' => 'Three of Swords', 'name_zh' => '寶劍三', 'keywords_upright' => '心碎,悲傷,背叛,痛苦', 'keywords_reversed' => '療癒,寬恕,恢復'],
            ['number' => 4, 'name' => 'Four of Swords', 'name_zh' => '寶劍四', 'keywords_upright' => '休息,恢復,冥想,思考', 'keywords_reversed' => '倦怠,疲憊,壓力'],
            ['number' => 5, 'name' => 'Five of Swords', 'name_zh' => '寶劍五', 'keywords_upright' => '衝突,失敗,爭執,勝利', 'keywords_reversed' => '和解,原諒,彌補'],
            ['number' => 6, 'name' => 'Six of Swords', 'name_zh' => '寶劍六', 'keywords_upright' => '轉變,旅行,遠離困境,療癒', 'keywords_reversed' => '抗拒改變,停滯,未解決'],
            ['number' => 7, 'name' => 'Seven of Swords', 'name_zh' => '寶劍七', 'keywords_upright' => '欺騙,策略,逃避,秘密', 'keywords_reversed' => '真相揭露,誠實,承認'],
            ['number' => 8, 'name' => 'Eight of Swords', 'name_zh' => '寶劍八', 'keywords_upright' => '限制,恐懼,束縛,受害者心態', 'keywords_reversed' => '釋放,自由,新視角'],
            ['number' => 9, 'name' => 'Nine of Swords', 'name_zh' => '寶劍九', 'keywords_upright' => '焦慮,噩夢,恐懼,擔憂', 'keywords_reversed' => '希望,療癒,釋放恐懼'],
            ['number' => 10, 'name' => 'Ten of Swords', 'name_zh' => '寶劍十', 'keywords_upright' => '結束,失敗,背叛,痛苦', 'keywords_reversed' => '恢復,重生,新開始'],
            ['number' => 11, 'name' => 'Page of Swords', 'name_zh' => '寶劍侍者', 'keywords_upright' => '好奇,警覺,熱情,交流', 'keywords_reversed' => '八卦,間諜,欺騙'],
            ['number' => 12, 'name' => 'Knight of Swords', 'name_zh' => '寶劍騎士', 'keywords_upright' => '雄心,行動,衝動,直接', 'keywords_reversed' => '魯莽,不耐煩,缺乏計劃'],
            ['number' => 13, 'name' => 'Queen of Swords', 'name_zh' => '寶劍王后', 'keywords_upright' => '獨立,公正,清晰,直率', 'keywords_reversed' => '冷酷,苦澀,殘酷'],
            ['number' => 14, 'name' => 'King of Swords', 'name_zh' => '寶劍國王', 'keywords_upright' => '智慧,權威,真相,理性', 'keywords_reversed' => '操縱,殘酷,濫用權力'],
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
            ['number' => 1, 'name' => 'Ace of Pentacles', 'name_zh' => '錢幣王牌', 'keywords_upright' => '機會,繁榮,新事業,顯化', 'keywords_reversed' => '失去機會,財務不穩,貪婪'],
            ['number' => 2, 'name' => 'Two of Pentacles', 'name_zh' => '錢幣二', 'keywords_upright' => '平衡,靈活性,多工,適應', 'keywords_reversed' => '失衡,不知所措,混亂'],
            ['number' => 3, 'name' => 'Three of Pentacles', 'name_zh' => '錢幣三', 'keywords_upright' => '團隊合作,合作,技能,學習', 'keywords_reversed' => '缺乏合作,不和諧,衝突'],
            ['number' => 4, 'name' => 'Four of Pentacles', 'name_zh' => '錢幣四', 'keywords_upright' => '控制,安全,節儉,執著', 'keywords_reversed' => '貪婪,物質主義,自私'],
            ['number' => 5, 'name' => 'Five of Pentacles', 'name_zh' => '錢幣五', 'keywords_upright' => '財務困難,貧困,孤立,焦慮', 'keywords_reversed' => '恢復,改善,希望'],
            ['number' => 6, 'name' => 'Six of Pentacles', 'name_zh' => '錢幣六', 'keywords_upright' => '慷慨,給予,分享,慈善', 'keywords_reversed' => '債務,自私,單向付出'],
            ['number' => 7, 'name' => 'Seven of Pentacles', 'name_zh' => '錢幣七', 'keywords_upright' => '評估,耐心,長期願景,堅持', 'keywords_reversed' => '缺乏耐心,焦慮,延遲'],
            ['number' => 8, 'name' => 'Eight of Pentacles', 'name_zh' => '錢幣八', 'keywords_upright' => '勤奮,技能發展,完美主義,努力', 'keywords_reversed' => '缺乏專注,倦怠,品質低'],
            ['number' => 9, 'name' => 'Nine of Pentacles', 'name_zh' => '錢幣九', 'keywords_upright' => '獨立,豐盛,成就,自給自足', 'keywords_reversed' => '過度依賴,財務不穩,孤獨'],
            ['number' => 10, 'name' => 'Ten of Pentacles', 'name_zh' => '錢幣十', 'keywords_upright' => '財富,遺產,家庭,穩定', 'keywords_reversed' => '財務失敗,破產,家庭問題'],
            ['number' => 11, 'name' => 'Page of Pentacles', 'name_zh' => '錢幣侍者', 'keywords_upright' => '勤奮,目標,野心,好消息', 'keywords_reversed' => '缺乏進展,拖延,不切實際'],
            ['number' => 12, 'name' => 'Knight of Pentacles', 'name_zh' => '錢幣騎士', 'keywords_upright' => '責任,效率,保守,可靠', 'keywords_reversed' => '懶惰,完美主義,僵化'],
            ['number' => 13, 'name' => 'Queen of Pentacles', 'name_zh' => '錢幣王后', 'keywords_upright' => '實際,滋養,穩定,財務安全', 'keywords_reversed' => '自私,嫉妒,物質主義'],
            ['number' => 14, 'name' => 'King of Pentacles', 'name_zh' => '錢幣國王', 'keywords_upright' => '富裕,商業成功,領導,安全', 'keywords_reversed' => '貪婪,頑固,腐敗'],
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
