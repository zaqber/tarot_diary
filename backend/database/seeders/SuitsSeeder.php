<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SuitsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $suits = [
            [
                'name' => 'Wands',
                'name_zh' => '權杖',
                'element' => 'Fire',
                'description' => '代表行動、熱情、創造力和企圖心。與事業、冒險和個人成長有關。',
            ],
            [
                'name' => 'Cups',
                'name_zh' => '聖杯',
                'element' => 'Water',
                'description' => '代表情感、關係、直覺和靈性。與愛情、友誼和內心感受有關。',
            ],
            [
                'name' => 'Swords',
                'name_zh' => '寶劍',
                'element' => 'Air',
                'description' => '代表思考、溝通、真相和挑戰。與決策、衝突和心智活動有關。',
            ],
            [
                'name' => 'Pentacles',
                'name_zh' => '錢幣',
                'element' => 'Earth',
                'description' => '代表物質、財富、工作和安全感。與金錢、健康和實際事務有關。',
            ],
        ];

        DB::table('suits')->insert($suits);
    }
}
