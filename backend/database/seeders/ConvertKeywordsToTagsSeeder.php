<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ConvertKeywordsToTagsSeeder extends Seeder
{
    /**
     * 將 tarot_cards 表中的 keywords_upright 和 keywords_reversed
     * 轉換為 tags，並建立 card_tags 關聯
     */
    public function run(): void
    {
        $this->command->info('🔄 開始轉換關鍵字為標籤...');
        
        // 獲取所有塔羅牌
        $cards = DB::table('tarot_cards')->get();
        
        $createdTags = 0;
        $createdRelations = 0;
        $skippedRelations = 0;
        
        foreach ($cards as $card) {
            // 處理正位關鍵字
            if (!empty($card->keywords_upright)) {
                $uprightKeywords = explode(',', $card->keywords_upright);
                foreach ($uprightKeywords as $keyword) {
                    $keyword = trim($keyword);
                    if (empty($keyword)) continue;
                    
                    $result = $this->createTagAndRelation(
                        $card->id,
                        $keyword,
                        'upright'
                    );
                    
                    if ($result['tag_created']) $createdTags++;
                    if ($result['relation_created']) {
                        $createdRelations++;
                    } else {
                        $skippedRelations++;
                    }
                }
            }
            
            // 處理逆位關鍵字
            if (!empty($card->keywords_reversed)) {
                $reversedKeywords = explode(',', $card->keywords_reversed);
                foreach ($reversedKeywords as $keyword) {
                    $keyword = trim($keyword);
                    if (empty($keyword)) continue;
                    
                    $result = $this->createTagAndRelation(
                        $card->id,
                        $keyword,
                        'reversed'
                    );
                    
                    if ($result['tag_created']) $createdTags++;
                    if ($result['relation_created']) {
                        $createdRelations++;
                    } else {
                        $skippedRelations++;
                    }
                }
            }
        }
        
        $this->command->info('');
        $this->command->info('✅ 轉換完成！');
        $this->command->info("   新建標籤: {$createdTags} 個");
        $this->command->info("   新建關聯: {$createdRelations} 筆");
        $this->command->info("   跳過重複: {$skippedRelations} 筆");
        
        // 顯示統計
        $totalTags = DB::table('tags')->count();
        $totalRelations = DB::table('card_tags')->count();
        $this->command->info('');
        $this->command->info("📊 目前統計:");
        $this->command->info("   標籤總數: {$totalTags}");
        $this->command->info("   關聯總數: {$totalRelations}");
    }
    
    /**
     * 創建或獲取標籤，並建立與牌卡的關聯
     */
    private function createTagAndRelation($cardId, $keyword, $position)
    {
        $tagCreated = false;
        $relationCreated = false;
        
        // 1. 檢查標籤是否已存在（先用中文名稱查找）
        $tag = DB::table('tags')
            ->where('name_zh', $keyword)
            ->first();
        
        // 2. 如果標籤不存在，嘗試創建新標籤
        if (!$tag) {
            $englishName = $this->transliterate($keyword);
            
            // 檢查英文名稱是否已存在
            $tagByEnglishName = DB::table('tags')
                ->where('name', $englishName)
                ->first();
            
            if ($tagByEnglishName) {
                // 如果英文名已存在，使用該標籤並更新中文名（如果為空）
                $tagId = $tagByEnglishName->id;
                if (empty($tagByEnglishName->name_zh)) {
                    DB::table('tags')
                        ->where('id', $tagId)
                        ->update(['name_zh' => $keyword]);
                }
            } else {
                // 創建新標籤
                $tagId = DB::table('tags')->insertGetId([
                    'name' => $englishName,
                    'name_zh' => $keyword,
                    'category' => $this->guessCategory($keyword),
                    'emotion_type' => $this->guessEmotionType($keyword),
                    'color' => $this->generateColor($keyword),
                ]);
                $tagCreated = true;
            }
        } else {
            $tagId = $tag->id;
        }
        
        // 3. 檢查關聯是否已存在
        $existingRelation = DB::table('card_tags')
            ->where('card_id', $cardId)
            ->where('tag_id', $tagId)
            ->where('position', $position)
            ->whereNull('user_id')
            ->exists();
        
        // 4. 如果關聯不存在，創建新關聯
        if (!$existingRelation) {
            DB::table('card_tags')->insert([
                'card_id' => $cardId,
                'tag_id' => $tagId,
                'position' => $position,
                'is_default' => true,
                'user_id' => null,
            ]);
            $relationCreated = true;
        }
        
        return [
            'tag_created' => $tagCreated,
            'relation_created' => $relationCreated,
        ];
    }
    
    /**
     * 簡單的中文轉拼音（用於 name 欄位）
     */
    private function transliterate($chinese)
    {
        // 這裡簡化處理，實際應用可以使用專門的拼音庫
        // 暫時使用簡化版本
        $map = [
            '靈感' => 'inspiration',
            '新機會' => 'new_opportunity',
            '創意' => 'creativity',
            '潛力' => 'potential',
            '缺乏方向' => 'lack_direction',
            '延遲' => 'delay',
            '錯失機會' => 'missed_opportunity',
            '計劃' => 'planning',
            '決策' => 'decision_making',
            '未來願景' => 'future_vision',
            '進展' => 'progress',
            '擴展' => 'expansion',
            '遠見' => 'foresight',
            '機會' => 'opportunity',
            '成長' => 'growth',
            '慶祝' => 'celebration',
            '和諧' => 'harmony',
            '婚禮' => 'wedding',
            '穩定' => 'stability',
            '競爭' => 'competition',
            '衝突' => 'conflict',
            '緊張' => 'tension',
            '挑戰' => 'challenge',
            '勝利' => 'victory',
            '認可' => 'recognition',
            '成功' => 'success',
            '驕傲' => 'pride',
            '堅持' => 'perseverance',
            '防衛' => 'defense',
            '毅力' => 'determination',
            '速度' => 'speed',
            '行動' => 'action',
            '快速變化' => 'rapid_change',
            '旅行' => 'travel',
            '韌性' => 'resilience',
            '勇氣' => 'courage',
            '負擔' => 'burden',
            '責任' => 'responsibility',
            '壓力' => 'pressure',
            '努力' => 'effort',
            '探索' => 'exploration',
            '熱情' => 'passion',
            '自由精神' => 'free_spirit',
            '好消息' => 'good_news',
            '能量' => 'energy',
            '衝動' => 'impulsive',
            '冒險' => 'adventure',
            '自信' => 'confidence',
            '獨立' => 'independence',
            '魅力' => 'charm',
            '領導' => 'leadership',
            '願景' => 'vision',
            '企業家精神' => 'entrepreneurship',
            '榮譽' => 'honor',
            '新戀情' => 'new_love',
            '直覺' => 'intuition',
            '愛' => 'love',
            '伴侶關係' => 'partnership',
            '連結' => 'connection',
            '友誼' => 'friendship',
            '社交' => 'social',
            '喜悅' => 'joy',
            '冥想' => 'meditation',
            '沉思' => 'contemplation',
            '無聊' => 'boredom',
            '不滿' => 'dissatisfaction',
            '失望' => 'disappointment',
            '悲傷' => 'sadness',
            '後悔' => 'regret',
            '失落' => 'loss',
            '懷舊' => 'nostalgia',
            '童年' => 'childhood',
            '天真' => 'innocence',
            '選擇' => 'choices',
            '幻想' => 'fantasy',
            '幻覺' => 'illusion',
            '願望' => 'wishes',
            '離開' => 'leaving',
            '撤退' => 'withdrawal',
            '尋求真理' => 'seeking_truth',
            '滿足' => 'contentment',
            '願望成真' => 'wish_fulfilled',
            '豐盛' => 'abundance',
            '家庭和諧' => 'family_harmony',
            '幸福' => 'happiness',
            '情感圓滿' => 'emotional_fulfillment',
            '敏感' => 'sensitivity',
            '浪漫' => 'romance',
            '想像力' => 'imagination',
            '理想主義' => 'idealism',
            '慈悲' => 'compassion',
            '溫暖' => 'warmth',
            '支持' => 'support',
            '情緒平衡' => 'emotional_balance',
            '外交' => 'diplomacy',
            '智慧' => 'wisdom',
            '突破' => 'breakthrough',
            '清晰' => 'clarity',
            '真相' => 'truth',
            '新想法' => 'new_ideas',
            '困難決擇' => 'difficult_choice',
            '僵局' => 'stalemate',
            '平衡' => 'balance',
            '迴避' => 'avoidance',
            '心碎' => 'heartbreak',
            '背叛' => 'betrayal',
            '痛苦' => 'pain',
            '休息' => 'rest',
            '恢復' => 'recovery',
            '思考' => 'reflection',
            '失敗' => 'failure',
            '爭執' => 'dispute',
            '轉變' => 'transition',
            '遠離困境' => 'moving_away',
            '療癒' => 'healing',
            '欺騙' => 'deception',
            '策略' => 'strategy',
            '逃避' => 'escape',
            '秘密' => 'secrets',
            '限制' => 'restriction',
            '恐懼' => 'fear',
            '束縛' => 'trapped',
            '受害者心態' => 'victim_mentality',
            '焦慮' => 'anxiety',
            '噩夢' => 'nightmare',
            '擔憂' => 'worry',
            '結束' => 'ending',
            '好奇' => 'curiosity',
            '警覺' => 'alertness',
            '交流' => 'communication',
            '雄心' => 'ambition',
            '直接' => 'directness',
            '公正' => 'justice',
            '直率' => 'straightforward',
            '權威' => 'authority',
            '理性' => 'rationality',
            '繁榮' => 'prosperity',
            '新事業' => 'new_venture',
            '顯化' => 'manifestation',
            '靈活性' => 'flexibility',
            '多工' => 'multitasking',
            '適應' => 'adaptation',
            '團隊合作' => 'teamwork',
            '合作' => 'cooperation',
            '技能' => 'skills',
            '學習' => 'learning',
            '控制' => 'control',
            '安全' => 'security',
            '節儉' => 'frugality',
            '執著' => 'attachment',
            '財務困難' => 'financial_difficulty',
            '貧困' => 'poverty',
            '孤立' => 'isolation',
            '慷慨' => 'generosity',
            '給予' => 'giving',
            '分享' => 'sharing',
            '慈善' => 'charity',
            '評估' => 'assessment',
            '耐心' => 'patience',
            '長期願景' => 'long_term_vision',
            '勤奮' => 'diligence',
            '技能發展' => 'skill_development',
            '完美主義' => 'perfectionism',
            '豐盛' => 'abundance',
            '成就' => 'achievement',
            '自給自足' => 'self_sufficiency',
            '財富' => 'wealth',
            '遺產' => 'legacy',
            '家庭' => 'family',
            '目標' => 'goals',
            '野心' => 'ambition',
            '效率' => 'efficiency',
            '保守' => 'conservative',
            '可靠' => 'reliable',
            '實際' => 'practical',
            '滋養' => 'nurturing',
            '財務安全' => 'financial_security',
            '富裕' => 'prosperity',
            '商業成功' => 'business_success',
            // 添加更多映射...
        ];
        
        return $map[$chinese] ?? strtolower(str_replace([' ', '　'], '_', $chinese));
    }
    
    /**
     * 根據關鍵字猜測分類
     */
    private function guessCategory($keyword)
    {
        $emotionKeywords = ['喜悅', '悲傷', '焦慮', '恐懼', '憤怒', '快樂', '愛', '希望', '失望', '後悔', '滿足', '幸福', '痛苦', '心碎', '擔憂'];
        $eventKeywords = ['工作', '旅行', '慶祝', '衝突', '決定', '新開始', '結束', '挑戰', '成就', '突破', '轉變', '離開'];
        $situationKeywords = ['成長', '療癒', '穩定', '混亂', '機會', '障礙', '反思', '行動', '等待', '轉化', '平衡'];
        $careerKeywords = ['成功', '失敗', '升遷', '專案', '團隊合作', '領導', '創意', '技能', '效率'];
        
        if (in_array($keyword, $emotionKeywords)) return 'emotion';
        if (in_array($keyword, $eventKeywords)) return 'event';
        if (in_array($keyword, $situationKeywords)) return 'situation';
        if (in_array($keyword, $careerKeywords)) return 'career';
        
        return 'general';
    }
    
    /**
     * 根據關鍵字猜測情緒類型
     */
    private function guessEmotionType($keyword)
    {
        $positiveKeywords = ['喜悅', '快樂', '愛', '希望', '成功', '勝利', '慶祝', '滿足', '幸福', '豐盛', '成就', '突破', '療癒', '和諧', '自信'];
        $negativeKeywords = ['悲傷', '焦慮', '恐懼', '憤怒', '失望', '後悔', '痛苦', '心碎', '失敗', '衝突', '背叛', '擔憂', '限制', '束縛', '孤立'];
        
        if (in_array($keyword, $positiveKeywords)) return 'positive';
        if (in_array($keyword, $negativeKeywords)) return 'negative';
        
        return 'neutral';
    }
    
    /**
     * 根據關鍵字生成顏色代碼
     */
    private function generateColor($keyword)
    {
        $emotionType = $this->guessEmotionType($keyword);
        
        $colorMap = [
            'positive' => ['#FFD700', '#FFA500', '#FF69B4', '#98FB98', '#87CEEB', '#DDA0DD'],
            'negative' => ['#B22222', '#8B0000', '#DC143C', '#4682B4', '#696969', '#2F4F4F'],
            'neutral' => ['#B0C4DE', '#D3D3D3', '#9370DB', '#778899', '#5F9EA0', '#8A2BE2'],
        ];
        
        $colors = $colorMap[$emotionType] ?? $colorMap['neutral'];
        return $colors[array_rand($colors)];
    }
}
