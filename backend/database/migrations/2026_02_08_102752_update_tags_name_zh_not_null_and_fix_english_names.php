<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * 中文 → 英文名稱映射表
     */
    private function getTranslationMap(): array
    {
        return [
            '自由' => 'freedom',
            '魯莽' => 'recklessness',
            '不負責任' => 'irresponsibility',
            '風險' => 'risk',
            '創造力' => 'creative_power',
            '行動力' => 'action_power',
            '操縱' => 'manipulation',
            '詭計' => 'trickery',
            '缺乏行動' => 'lack_of_action',
            '神秘' => 'mystery',
            '內在智慧' => 'inner_wisdom',
            '潛意識' => 'subconscious',
            '隱藏秘密' => 'hidden_secrets',
            '缺乏洞察' => 'lack_of_insight',
            '迷惑' => 'bewilderment',
            '豐饒' => 'fertility',
            '母性' => 'motherhood',
            '創造' => 'creation',
            '自然' => 'nature',
            '依賴' => 'dependence',
            '窒息' => 'suffocation',
            '缺乏成長' => 'lack_of_growth',
            '結構' => 'structure',
            '專制' => 'authoritarianism',
            '控制過度' => 'excessive_control',
            '缺乏紀律' => 'lack_of_discipline',
            '傳統' => 'tradition',
            '教育' => 'education',
            '信仰' => 'faith',
            '叛逆' => 'rebellion',
            '非傳統' => 'non_traditional',
            '質疑規則' => 'questioning_rules',
            '愛情' => 'romantic_love',
            '關係' => 'relationship',
            '不和諧' => 'disharmony',
            '錯誤選擇' => 'wrong_choice',
            '意志力' => 'willpower',
            '掌控' => 'mastery',
            '前進' => 'moving_forward',
            '失控' => 'loss_of_control',
            '挫敗' => 'defeat',
            '內在力量' => 'inner_strength',
            '自我懷疑' => 'self_doubt',
            '脆弱' => 'vulnerability',
            '缺乏信心' => 'lack_of_confidence',
            '內省' => 'introspection',
            '循環' => 'cycle',
            '命運' => 'destiny',
            '轉機' => 'turning_point',
            '壞運氣' => 'bad_luck',
            '抗拒改變' => 'resistance_to_change',
            '公平' => 'fairness',
            '法律' => 'law',
            '不公正' => 'injustice',
            '偏見' => 'prejudice',
            '逃避責任' => 'avoiding_responsibility',
            '放手' => 'letting_go',
            '新視角' => 'new_perspective',
            '犧牲' => 'sacrifice',
            '拖延' => 'procrastination',
            '抗拒' => 'resistance',
            '無謂犧牲' => 'pointless_sacrifice',
            '重生' => 'rebirth',
            '放下' => 'release',
            '停滯' => 'stagnation',
            '無法放手' => 'inability_to_let_go',
            '節制' => 'temperance',
            '不平衡' => 'imbalance',
            '過度' => 'excess',
            '缺乏和諧' => 'lack_of_harmony',
            '誘惑' => 'temptation',
            '物質主義' => 'materialism',
            '成癮' => 'addiction',
            '釋放' => 'liberation',
            '覺醒' => 'awakening',
            '擺脫束縛' => 'breaking_free',
            '突變' => 'upheaval',
            '啟示' => 'revelation',
            '崩潰' => 'breakdown',
            '逃避災難' => 'avoiding_disaster',
            '恐懼改變' => 'fear_of_change',
            '寧靜' => 'serenity',
            '失去信心' => 'loss_of_faith',
            '絕望' => 'despair',
            '缺乏動力' => 'lack_of_motivation',
            '幻象' => 'mirage',
            '釋放恐懼' => 'releasing_fear',
            '真相揭露' => 'truth_revealed',
            '活力' => 'vitality',
            '真實' => 'authenticity',
            '過度樂觀' => 'excessive_optimism',
            '延遲快樂' => 'delayed_happiness',
            '負面' => 'negativity',
            '未完結' => 'incomplete',
            '完成' => 'completion',
            '整合' => 'integration',
            '圓滿' => 'fulfillment',
            '未完成' => 'unfinished',
            '缺乏閉合' => 'lack_of_closure',
            '尋求閉環' => 'seeking_closure',
            '恐懼未知' => 'fear_of_unknown',
            '缺乏計劃' => 'lack_of_planning',
            '猶豫不決' => 'indecisiveness',
            '計劃受阻' => 'blocked_plans',
            '缺乏遠見' => 'lack_of_vision',
            '不穩定' => 'instability',
            '缺乏支持' => 'lack_of_support',
            '延遲慶祝' => 'delayed_celebration',
            '避免衝突' => 'avoiding_conflict',
            '內在衝突' => 'inner_conflict',
            '自負' => 'arrogance',
            '缺乏認可' => 'lack_of_recognition',
            '不知所措' => 'overwhelmed',
            '放棄' => 'giving_up',
            '疲憊' => 'exhaustion',
            '防備' => 'defensiveness',
            '偏執' => 'paranoia',
            '固執' => 'stubbornness',
            '卸下重擔' => 'laying_down_burdens',
            '委派' => 'delegation',
            '倦怠' => 'burnout',
            '壞消息' => 'bad_news',
            '不耐煩' => 'impatience',
            '自私' => 'selfishness',
            '嫉妒' => 'jealousy',
            '不安全感' => 'insecurity',
            '專橫' => 'domineering',
            '殘酷' => 'cruelty',
            '情感封閉' => 'emotional_shutdown',
            '壓抑情感' => 'suppressed_emotions',
            '冷漠' => 'indifference',
            '過度放縱' => 'overindulgence',
            '三角關係' => 'love_triangle',
            '動機' => 'motivation',
            '重新評估' => 'reevaluation',
            '接受' => 'acceptance',
            '寬恕' => 'forgiveness',
            '活在過去' => 'living_in_past',
            '不成熟' => 'immaturity',
            '困在過去' => 'stuck_in_past',
            '專注' => 'focus',
            '貪婪' => 'greed',
            '家庭問題' => 'family_issues',
            '分裂' => 'division',
            '價值觀衝突' => 'values_conflict',
            '情緒不成熟' => 'emotional_immaturity',
            '幼稚' => 'childishness',
            '不切實際' => 'unrealistic',
            '情緒化' => 'emotional',
            '情緒不穩' => 'emotional_instability',
            '情緒操縱' => 'emotional_manipulation',
            '波動' => 'fluctuation',
            '誤解' => 'misunderstanding',
            '優柔寡斷' => 'indecision',
            '信息釋放' => 'information_release',
            '解決' => 'resolution',
            '原諒' => 'pardon',
            '彌補' => 'making_amends',
            '未解決' => 'unresolved',
            '誠實' => 'honesty',
            '承認' => 'acknowledgment',
            '八卦' => 'gossip',
            '間諜' => 'espionage',
            '冷酷' => 'coldness',
            '苦澀' => 'bitterness',
            '濫用權力' => 'abuse_of_power',
            '失去機會' => 'lost_opportunity',
            '財務不穩' => 'financial_instability',
            '缺乏合作' => 'lack_of_cooperation',
            '改善' => 'improvement',
            '債務' => 'debt',
            '單向付出' => 'one_sided_effort',
            '缺乏耐心' => 'lack_of_patience',
            '缺乏專注' => 'lack_of_focus',
            '品質低' => 'poor_quality',
            '過度依賴' => 'over_dependence',
            '財務失敗' => 'financial_failure',
            '破產' => 'bankruptcy',
            '缺乏進展' => 'lack_of_progress',
            '懶惰' => 'laziness',
            '僵化' => 'rigidity',
            '頑固' => 'obstinacy',
            '腐敗' => 'corruption',
            'string' => 'string_tag',
        ];
    }

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. 修正現有資料：將 name 與 name_zh 相同的記錄更新為英文 name
        $map = $this->getTranslationMap();

        foreach ($map as $chinese => $english) {
            // 檢查目標英文名是否已存在（避免 unique 衝突）
            $existingEnglish = DB::table('tags')->where('name', $english)->first();
            if ($existingEnglish) {
                // 如果英文名已被其他 tag 使用，加上後綴
                $english = $english . '_alt';
            }

            DB::table('tags')
                ->where('name', $chinese)
                ->where('name_zh', $chinese)
                ->update(['name' => $english]);
        }

        // 2. 將 name_zh 欄位改為 NOT NULL
        Schema::table('tags', function (Blueprint $table) {
            $table->string('name_zh', 50)->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tags', function (Blueprint $table) {
            $table->string('name_zh', 50)->nullable()->change();
        });
    }
};
