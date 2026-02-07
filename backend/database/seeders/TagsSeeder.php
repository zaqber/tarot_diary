<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TagsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tags = [
            // 情緒標籤
            ['name' => 'joy', 'name_zh' => '喜悅', 'category' => 'emotion', 'emotion_type' => 'positive', 'color' => '#FFD700'],
            ['name' => 'happiness', 'name_zh' => '快樂', 'category' => 'emotion', 'emotion_type' => 'positive', 'color' => '#FFA500'],
            ['name' => 'love', 'name_zh' => '愛', 'category' => 'emotion', 'emotion_type' => 'positive', 'color' => '#FF69B4'],
            ['name' => 'peace', 'name_zh' => '平靜', 'category' => 'emotion', 'emotion_type' => 'positive', 'color' => '#87CEEB'],
            ['name' => 'hope', 'name_zh' => '希望', 'category' => 'emotion', 'emotion_type' => 'positive', 'color' => '#98FB98'],
            ['name' => 'confidence', 'name_zh' => '自信', 'category' => 'emotion', 'emotion_type' => 'positive', 'color' => '#DAA520'],
            ['name' => 'gratitude', 'name_zh' => '感恩', 'category' => 'emotion', 'emotion_type' => 'positive', 'color' => '#DDA0DD'],
            
            ['name' => 'sadness', 'name_zh' => '悲傷', 'category' => 'emotion', 'emotion_type' => 'negative', 'color' => '#4682B4'],
            ['name' => 'anxiety', 'name_zh' => '焦慮', 'category' => 'emotion', 'emotion_type' => 'negative', 'color' => '#B22222'],
            ['name' => 'fear', 'name_zh' => '恐懼', 'category' => 'emotion', 'emotion_type' => 'negative', 'color' => '#8B0000'],
            ['name' => 'anger', 'name_zh' => '憤怒', 'category' => 'emotion', 'emotion_type' => 'negative', 'color' => '#DC143C'],
            ['name' => 'confusion', 'name_zh' => '困惑', 'category' => 'emotion', 'emotion_type' => 'negative', 'color' => '#696969'],
            ['name' => 'frustration', 'name_zh' => '挫折', 'category' => 'emotion', 'emotion_type' => 'negative', 'color' => '#8B4513'],
            ['name' => 'loneliness', 'name_zh' => '孤獨', 'category' => 'emotion', 'emotion_type' => 'negative', 'color' => '#2F4F4F'],
            ['name' => 'regret', 'name_zh' => '後悔', 'category' => 'emotion', 'emotion_type' => 'negative', 'color' => '#708090'],
            
            ['name' => 'calm', 'name_zh' => '冷靜', 'category' => 'emotion', 'emotion_type' => 'neutral', 'color' => '#B0C4DE'],
            ['name' => 'neutral', 'name_zh' => '中性', 'category' => 'emotion', 'emotion_type' => 'neutral', 'color' => '#D3D3D3'],
            ['name' => 'contemplative', 'name_zh' => '沉思', 'category' => 'emotion', 'emotion_type' => 'neutral', 'color' => '#9370DB'],
            
            ['name' => 'mixed_feelings', 'name_zh' => '複雜情緒', 'category' => 'emotion', 'emotion_type' => 'mixed', 'color' => '#BC8F8F'],
            ['name' => 'bittersweet', 'name_zh' => '苦樂參半', 'category' => 'emotion', 'emotion_type' => 'mixed', 'color' => '#CD853F'],

            // 事件標籤
            ['name' => 'work', 'name_zh' => '工作', 'category' => 'event', 'emotion_type' => 'neutral', 'color' => '#4169E1'],
            ['name' => 'career', 'name_zh' => '事業', 'category' => 'event', 'emotion_type' => 'neutral', 'color' => '#483D8B'],
            ['name' => 'study', 'name_zh' => '學習', 'category' => 'event', 'emotion_type' => 'neutral', 'color' => '#5F9EA0'],
            ['name' => 'travel', 'name_zh' => '旅行', 'category' => 'event', 'emotion_type' => 'positive', 'color' => '#20B2AA'],
            ['name' => 'meeting', 'name_zh' => '會議', 'category' => 'event', 'emotion_type' => 'neutral', 'color' => '#778899'],
            ['name' => 'celebration', 'name_zh' => '慶祝', 'category' => 'event', 'emotion_type' => 'positive', 'color' => '#FF6347'],
            ['name' => 'conflict', 'name_zh' => '衝突', 'category' => 'event', 'emotion_type' => 'negative', 'color' => '#A52A2A'],
            ['name' => 'decision', 'name_zh' => '決定', 'category' => 'event', 'emotion_type' => 'neutral', 'color' => '#8A2BE2'],
            ['name' => 'change', 'name_zh' => '變化', 'category' => 'event', 'emotion_type' => 'neutral', 'color' => '#FF8C00'],
            ['name' => 'new_beginning', 'name_zh' => '新開始', 'category' => 'event', 'emotion_type' => 'positive', 'color' => '#32CD32'],
            ['name' => 'ending', 'name_zh' => '結束', 'category' => 'event', 'emotion_type' => 'neutral', 'color' => '#4B0082'],
            ['name' => 'challenge', 'name_zh' => '挑戰', 'category' => 'event', 'emotion_type' => 'neutral', 'color' => '#B8860B'],
            ['name' => 'achievement', 'name_zh' => '成就', 'category' => 'event', 'emotion_type' => 'positive', 'color' => '#FFD700'],
            ['name' => 'loss', 'name_zh' => '失落', 'category' => 'event', 'emotion_type' => 'negative', 'color' => '#191970'],
            ['name' => 'breakthrough', 'name_zh' => '突破', 'category' => 'event', 'emotion_type' => 'positive', 'color' => '#00CED1'],

            // 人物標籤
            ['name' => 'family', 'name_zh' => '家人', 'category' => 'person', 'emotion_type' => 'neutral', 'color' => '#CD5C5C'],
            ['name' => 'partner', 'name_zh' => '伴侶', 'category' => 'person', 'emotion_type' => 'positive', 'color' => '#FF1493'],
            ['name' => 'friend', 'name_zh' => '朋友', 'category' => 'person', 'emotion_type' => 'positive', 'color' => '#FFB6C1'],
            ['name' => 'colleague', 'name_zh' => '同事', 'category' => 'person', 'emotion_type' => 'neutral', 'color' => '#4682B4'],
            ['name' => 'stranger', 'name_zh' => '陌生人', 'category' => 'person', 'emotion_type' => 'neutral', 'color' => '#696969'],
            ['name' => 'mentor', 'name_zh' => '導師', 'category' => 'person', 'emotion_type' => 'positive', 'color' => '#9370DB'],
            ['name' => 'child', 'name_zh' => '孩子', 'category' => 'person', 'emotion_type' => 'neutral', 'color' => '#FFC0CB'],
            ['name' => 'authority', 'name_zh' => '權威人物', 'category' => 'person', 'emotion_type' => 'neutral', 'color' => '#2F4F4F'],

            // 關係標籤
            ['name' => 'romance', 'name_zh' => '浪漫', 'category' => 'relationship', 'emotion_type' => 'positive', 'color' => '#FF69B4'],
            ['name' => 'friendship', 'name_zh' => '友誼', 'category' => 'relationship', 'emotion_type' => 'positive', 'color' => '#FFA07A'],
            ['name' => 'family_bond', 'name_zh' => '親情', 'category' => 'relationship', 'emotion_type' => 'positive', 'color' => '#F08080'],
            ['name' => 'cooperation', 'name_zh' => '合作', 'category' => 'relationship', 'emotion_type' => 'positive', 'color' => '#20B2AA'],
            ['name' => 'tension', 'name_zh' => '緊張', 'category' => 'relationship', 'emotion_type' => 'negative', 'color' => '#B22222'],
            ['name' => 'betrayal', 'name_zh' => '背叛', 'category' => 'relationship', 'emotion_type' => 'negative', 'color' => '#8B0000'],
            ['name' => 'reconciliation', 'name_zh' => '和解', 'category' => 'relationship', 'emotion_type' => 'positive', 'color' => '#98FB98'],
            ['name' => 'separation', 'name_zh' => '分離', 'category' => 'relationship', 'emotion_type' => 'negative', 'color' => '#483D8B'],
            ['name' => 'connection', 'name_zh' => '連結', 'category' => 'relationship', 'emotion_type' => 'positive', 'color' => '#DDA0DD'],

            // 情境標籤
            ['name' => 'growth', 'name_zh' => '成長', 'category' => 'situation', 'emotion_type' => 'positive', 'color' => '#3CB371'],
            ['name' => 'healing', 'name_zh' => '療癒', 'category' => 'situation', 'emotion_type' => 'positive', 'color' => '#7FFFD4'],
            ['name' => 'transition', 'name_zh' => '過渡', 'category' => 'situation', 'emotion_type' => 'neutral', 'color' => '#9370DB'],
            ['name' => 'stability', 'name_zh' => '穩定', 'category' => 'situation', 'emotion_type' => 'positive', 'color' => '#8FBC8F'],
            ['name' => 'chaos', 'name_zh' => '混亂', 'category' => 'situation', 'emotion_type' => 'negative', 'color' => '#8B4513'],
            ['name' => 'opportunity', 'name_zh' => '機會', 'category' => 'situation', 'emotion_type' => 'positive', 'color' => '#FFD700'],
            ['name' => 'obstacle', 'name_zh' => '障礙', 'category' => 'situation', 'emotion_type' => 'negative', 'color' => '#696969'],
            ['name' => 'reflection', 'name_zh' => '反思', 'category' => 'situation', 'emotion_type' => 'neutral', 'color' => '#778899'],
            ['name' => 'action', 'name_zh' => '行動', 'category' => 'situation', 'emotion_type' => 'neutral', 'color' => '#FF6347'],
            ['name' => 'waiting', 'name_zh' => '等待', 'category' => 'situation', 'emotion_type' => 'neutral', 'color' => '#B0C4DE'],
            ['name' => 'transformation', 'name_zh' => '轉化', 'category' => 'situation', 'emotion_type' => 'neutral', 'color' => '#9932CC'],
            ['name' => 'balance', 'name_zh' => '平衡', 'category' => 'situation', 'emotion_type' => 'positive', 'color' => '#48D1CC'],
            ['name' => 'imbalance', 'name_zh' => '失衡', 'category' => 'situation', 'emotion_type' => 'negative', 'color' => '#A0522D'],

            // 職業標籤
            ['name' => 'success', 'name_zh' => '成功', 'category' => 'career', 'emotion_type' => 'positive', 'color' => '#FFD700'],
            ['name' => 'failure', 'name_zh' => '失敗', 'category' => 'career', 'emotion_type' => 'negative', 'color' => '#8B0000'],
            ['name' => 'promotion', 'name_zh' => '升遷', 'category' => 'career', 'emotion_type' => 'positive', 'color' => '#FF8C00'],
            ['name' => 'job_change', 'name_zh' => '換工作', 'category' => 'career', 'emotion_type' => 'neutral', 'color' => '#4682B4'],
            ['name' => 'project', 'name_zh' => '專案', 'category' => 'career', 'emotion_type' => 'neutral', 'color' => '#5F9EA0'],
            ['name' => 'teamwork', 'name_zh' => '團隊合作', 'category' => 'career', 'emotion_type' => 'positive', 'color' => '#20B2AA'],
            ['name' => 'leadership', 'name_zh' => '領導', 'category' => 'career', 'emotion_type' => 'positive', 'color' => '#8B4789'],
            ['name' => 'creativity', 'name_zh' => '創意', 'category' => 'career', 'emotion_type' => 'positive', 'color' => '#FF69B4'],
        ];

        DB::table('tags')->insert($tags);
    }
}
