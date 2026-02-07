<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SpreadTypesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 創建牌陣類型
        $spreadTypes = [
            [
                'name' => 'Single Card',
                'name_zh' => '單張牌',
                'description' => '最簡單的牌陣，適合每日指引或快速問題。抽一張牌獲得直接的訊息。',
                'card_count' => 1,
                'difficulty_level' => 'beginner',
                'is_active' => true,
            ],
            [
                'name' => 'Three Card Spread',
                'name_zh' => '三張牌陣',
                'description' => '經典的三張牌陣，可用於過去-現在-未來，或情況-行動-結果等多種解讀方式。',
                'card_count' => 3,
                'difficulty_level' => 'beginner',
                'is_active' => true,
            ],
            [
                'name' => 'Celtic Cross',
                'name_zh' => '塞爾特十字',
                'description' => '最著名和全面的牌陣之一，提供深入的洞察和多角度的分析。',
                'card_count' => 10,
                'difficulty_level' => 'advanced',
                'is_active' => true,
            ],
            [
                'name' => 'Relationship Spread',
                'name_zh' => '關係牌陣',
                'description' => '專門用於探討兩人關係的牌陣，深入了解雙方的想法、感受和關係走向。',
                'card_count' => 7,
                'difficulty_level' => 'intermediate',
                'is_active' => true,
            ],
            [
                'name' => 'Career Path',
                'name_zh' => '職業道路',
                'description' => '專注於事業發展和職業選擇的牌陣，幫助釐清職業方向和機會。',
                'card_count' => 5,
                'difficulty_level' => 'intermediate',
                'is_active' => true,
            ],
            [
                'name' => 'Year Ahead',
                'name_zh' => '未來一年',
                'description' => '十二張牌代表未來十二個月，適合在生日或新年時使用。',
                'card_count' => 12,
                'difficulty_level' => 'advanced',
                'is_active' => true,
            ],
            [
                'name' => 'Decision Making',
                'name_zh' => '決策牌陣',
                'description' => '幫助在兩個選擇之間做出決定，分析每個選項的優劣和可能結果。',
                'card_count' => 7,
                'difficulty_level' => 'intermediate',
                'is_active' => true,
            ],
        ];

        foreach ($spreadTypes as $spread) {
            $spreadId = DB::table('spread_types')->insertGetId($spread);
            
            // 為每個牌陣創建位置定義
            $this->createSpreadPositions($spreadId, $spread['name']);
        }
    }

    /**
     * 為每個牌陣創建位置定義
     */
    private function createSpreadPositions($spreadId, $spreadName)
    {
        $positions = [];

        switch ($spreadName) {
            case 'Single Card':
                $positions = [
                    ['position_number' => 1, 'position_name' => 'Daily Message', 'position_name_zh' => '今日訊息', 'description' => '今天的指引和重點'],
                ];
                break;

            case 'Three Card Spread':
                $positions = [
                    ['position_number' => 1, 'position_name' => 'Past', 'position_name_zh' => '過去', 'description' => '過去的影響和根源'],
                    ['position_number' => 2, 'position_name' => 'Present', 'position_name_zh' => '現在', 'description' => '當前的情況和挑戰'],
                    ['position_number' => 3, 'position_name' => 'Future', 'position_name_zh' => '未來', 'description' => '可能的結果和發展'],
                ];
                break;

            case 'Celtic Cross':
                $positions = [
                    ['position_number' => 1, 'position_name' => 'Present', 'position_name_zh' => '現況', 'description' => '目前的情況'],
                    ['position_number' => 2, 'position_name' => 'Challenge', 'position_name_zh' => '挑戰', 'description' => '面臨的障礙或挑戰'],
                    ['position_number' => 3, 'position_name' => 'Past', 'position_name_zh' => '過去', 'description' => '過去的影響'],
                    ['position_number' => 4, 'position_name' => 'Future', 'position_name_zh' => '未來', 'description' => '即將發生的事'],
                    ['position_number' => 5, 'position_name' => 'Above', 'position_name_zh' => '目標', 'description' => '最佳可能結果'],
                    ['position_number' => 6, 'position_name' => 'Below', 'position_name_zh' => '根基', 'description' => '潛意識影響'],
                    ['position_number' => 7, 'position_name' => 'Advice', 'position_name_zh' => '建議', 'description' => '應採取的態度'],
                    ['position_number' => 8, 'position_name' => 'External', 'position_name_zh' => '外在影響', 'description' => '環境和他人的影響'],
                    ['position_number' => 9, 'position_name' => 'Hopes/Fears', 'position_name_zh' => '希望與恐懼', 'description' => '內心的期待或擔憂'],
                    ['position_number' => 10, 'position_name' => 'Outcome', 'position_name_zh' => '結果', 'description' => '最終的結果'],
                ];
                break;

            case 'Relationship Spread':
                $positions = [
                    ['position_number' => 1, 'position_name' => 'You', 'position_name_zh' => '你', 'description' => '你在關係中的狀態'],
                    ['position_number' => 2, 'position_name' => 'Your Partner', 'position_name_zh' => '對方', 'description' => '對方在關係中的狀態'],
                    ['position_number' => 3, 'position_name' => 'Connection', 'position_name_zh' => '連結', 'description' => '雙方的連結和共同點'],
                    ['position_number' => 4, 'position_name' => 'Challenge', 'position_name_zh' => '挑戰', 'description' => '關係中的障礙'],
                    ['position_number' => 5, 'position_name' => 'Past', 'position_name_zh' => '過去', 'description' => '關係的歷史影響'],
                    ['position_number' => 6, 'position_name' => 'Future', 'position_name_zh' => '未來', 'description' => '關係的可能發展'],
                    ['position_number' => 7, 'position_name' => 'Advice', 'position_name_zh' => '建議', 'description' => '如何改善關係'],
                ];
                break;

            case 'Career Path':
                $positions = [
                    ['position_number' => 1, 'position_name' => 'Current Situation', 'position_name_zh' => '目前狀況', 'description' => '當前的職業狀態'],
                    ['position_number' => 2, 'position_name' => 'Obstacles', 'position_name_zh' => '障礙', 'description' => '職業發展的阻礙'],
                    ['position_number' => 3, 'position_name' => 'Opportunities', 'position_name_zh' => '機會', 'description' => '可以把握的機會'],
                    ['position_number' => 4, 'position_name' => 'Action', 'position_name_zh' => '行動', 'description' => '應該採取的步驟'],
                    ['position_number' => 5, 'position_name' => 'Outcome', 'position_name_zh' => '結果', 'description' => '可能的職業發展'],
                ];
                break;

            case 'Year Ahead':
                $positions = [
                    ['position_number' => 1, 'position_name' => 'Month 1', 'position_name_zh' => '第一個月', 'description' => '第一個月的主題'],
                    ['position_number' => 2, 'position_name' => 'Month 2', 'position_name_zh' => '第二個月', 'description' => '第二個月的主題'],
                    ['position_number' => 3, 'position_name' => 'Month 3', 'position_name_zh' => '第三個月', 'description' => '第三個月的主題'],
                    ['position_number' => 4, 'position_name' => 'Month 4', 'position_name_zh' => '第四個月', 'description' => '第四個月的主題'],
                    ['position_number' => 5, 'position_name' => 'Month 5', 'position_name_zh' => '第五個月', 'description' => '第五個月的主題'],
                    ['position_number' => 6, 'position_name' => 'Month 6', 'position_name_zh' => '第六個月', 'description' => '第六個月的主題'],
                    ['position_number' => 7, 'position_name' => 'Month 7', 'position_name_zh' => '第七個月', 'description' => '第七個月的主題'],
                    ['position_number' => 8, 'position_name' => 'Month 8', 'position_name_zh' => '第八個月', 'description' => '第八個月的主題'],
                    ['position_number' => 9, 'position_name' => 'Month 9', 'position_name_zh' => '第九個月', 'description' => '第九個月的主題'],
                    ['position_number' => 10, 'position_name' => 'Month 10', 'position_name_zh' => '第十個月', 'description' => '第十個月的主題'],
                    ['position_number' => 11, 'position_name' => 'Month 11', 'position_name_zh' => '第十一個月', 'description' => '第十一個月的主題'],
                    ['position_number' => 12, 'position_name' => 'Month 12', 'position_name_zh' => '第十二個月', 'description' => '第十二個月的主題'],
                ];
                break;

            case 'Decision Making':
                $positions = [
                    ['position_number' => 1, 'position_name' => 'Situation', 'position_name_zh' => '情況', 'description' => '當前的決策情況'],
                    ['position_number' => 2, 'position_name' => 'Option A', 'position_name_zh' => '選項 A', 'description' => '第一個選擇'],
                    ['position_number' => 3, 'position_name' => 'Option B', 'position_name_zh' => '選項 B', 'description' => '第二個選擇'],
                    ['position_number' => 4, 'position_name' => 'Outcome A', 'position_name_zh' => '結果 A', 'description' => '選擇 A 的結果'],
                    ['position_number' => 5, 'position_name' => 'Outcome B', 'position_name_zh' => '結果 B', 'description' => '選擇 B 的結果'],
                    ['position_number' => 6, 'position_name' => 'Hidden Factor', 'position_name_zh' => '隱藏因素', 'description' => '未考慮到的因素'],
                    ['position_number' => 7, 'position_name' => 'Advice', 'position_name_zh' => '建議', 'description' => '決策指引'],
                ];
                break;
        }

        foreach ($positions as $position) {
            DB::table('spread_positions')->insert(array_merge($position, [
                'spread_type_id' => $spreadId,
            ]));
        }
    }
}
