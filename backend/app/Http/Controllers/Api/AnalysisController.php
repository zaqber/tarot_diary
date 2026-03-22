<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AnalysisController extends Controller
{
    /**
     * 分析儀表板（過去 N 天）
     *
     * 回傳：
     * 1) 正逆位分析
     * 2) 最常出現的牌 TOP5
     * 3) 花色／大牌分布
     * 4) 符合該日狀況（已點選標籤）的卡牌排行
     * 5) 關鍵字隨時間變化（以已點選標籤為主）
     *
     * GET /api/analysis/dashboard?days=30
     */
    public function dashboard(Request $request): JsonResponse
    {
        $userId = $request->user()->id;
        $days = (int) $request->input('days', 30);
        $days = max(1, min(365, $days));
        $since = now()->subDays($days)->startOfDay();

        $baseCards = DB::table('spread_readings as sr')
            ->join('spread_cards as sc', 'sr.id', '=', 'sc.spread_reading_id')
            ->where('sr.user_id', $userId)
            ->whereDate('sr.reading_date', '>=', $since);

        // 1) 正逆位
        $orientationRow = (clone $baseCards)
            ->selectRaw('SUM(CASE WHEN sc.is_reversed = 1 THEN 1 ELSE 0 END) as reversed_count')
            ->selectRaw('SUM(CASE WHEN sc.is_reversed = 0 THEN 1 ELSE 0 END) as upright_count')
            ->selectRaw('COUNT(*) as total')
            ->first();
        $orientation = [
            'upright_count' => (int) ($orientationRow->upright_count ?? 0),
            'reversed_count' => (int) ($orientationRow->reversed_count ?? 0),
            'total' => (int) ($orientationRow->total ?? 0),
        ];

        // 2) 最常出現的牌 TOP5
        $topCards = (clone $baseCards)
            ->join('tarot_cards as tc', 'sc.card_id', '=', 'tc.id')
            ->leftJoin('suits as s', 'tc.suit_id', '=', 's.id')
            ->select(
                'tc.id as card_id',
                'tc.name_zh',
                'tc.name',
                'tc.card_type',
                'tc.suit_id',
                's.name_zh as suit_name_zh'
            )
            ->selectRaw('COUNT(*) as count')
            ->groupBy('tc.id', 'tc.name_zh', 'tc.name', 'tc.card_type', 'tc.suit_id', 's.name_zh')
            ->orderByDesc('count')
            ->limit(5)
            ->get()
            ->map(fn ($r) => [
                'card_id' => (int) $r->card_id,
                'name_zh' => $r->name_zh,
                'name' => $r->name,
                'card_type' => $r->card_type,
                'suit_id' => $r->suit_id !== null ? (int) $r->suit_id : null,
                'suit_name_zh' => $r->suit_name_zh,
                'count' => (int) $r->count,
            ])->values()->toArray();

        // 3) 花色／大牌分布
        $distRows = (clone $baseCards)
            ->join('tarot_cards as tc', 'sc.card_id', '=', 'tc.id')
            ->leftJoin('suits as s', 'tc.suit_id', '=', 's.id')
            ->select('tc.card_type', 'tc.suit_id', 's.name_zh as suit_name_zh')
            ->selectRaw('COUNT(*) as count')
            ->groupBy('tc.card_type', 'tc.suit_id', 's.name_zh')
            ->get();
        $distCount = [
            'major' => 0,
            'wands' => 0,
            'cups' => 0,
            'swords' => 0,
            'pentacles' => 0,
        ];
        foreach ($distRows as $row) {
            $cnt = (int) $row->count;
            if ($row->card_type === 'major') {
                $distCount['major'] += $cnt;
                continue;
            }
            $nameZh = (string) ($row->suit_name_zh ?? '');
            if (str_contains($nameZh, '權杖')) {
                $distCount['wands'] += $cnt;
            } elseif (str_contains($nameZh, '聖杯')) {
                $distCount['cups'] += $cnt;
            } elseif (str_contains($nameZh, '寶劍')) {
                $distCount['swords'] += $cnt;
            } elseif (str_contains($nameZh, '錢幣')) {
                $distCount['pentacles'] += $cnt;
            }
        }
        $distribution = [
            ['key' => 'major', 'label' => '大牌', 'count' => $distCount['major'], 'color' => '#6D5BD0'],
            ['key' => 'wands', 'label' => '權杖', 'count' => $distCount['wands'], 'color' => '#E67E22'],
            ['key' => 'cups', 'label' => '聖杯', 'count' => $distCount['cups'], 'color' => '#3498DB'],
            ['key' => 'swords', 'label' => '寶劍', 'count' => $distCount['swords'], 'color' => '#95A5A6'],
            ['key' => 'pentacles', 'label' => '錢幣', 'count' => $distCount['pentacles'], 'color' => '#27AE60'],
        ];

        // 4) 符合該日狀況的卡牌排行（依已點選標籤次數）
        $selectedCards = DB::table('spread_readings as sr')
            ->join('spread_cards as sc', 'sr.id', '=', 'sc.spread_reading_id')
            ->join('spread_card_tag_selections as sts', 'sc.id', '=', 'sts.spread_card_id')
            ->join('tarot_cards as tc', 'sc.card_id', '=', 'tc.id')
            ->leftJoin('suits as s', 'tc.suit_id', '=', 's.id')
            ->where('sr.user_id', $userId)
            ->whereDate('sr.reading_date', '>=', $since)
            ->select(
                'tc.id as card_id',
                'tc.name_zh',
                'tc.name',
                'tc.card_type',
                'tc.suit_id',
                's.name_zh as suit_name_zh'
            )
            ->selectRaw('COUNT(sts.id) as selected_count')
            ->selectRaw('COUNT(DISTINCT sc.id) as hit_count')
            ->groupBy('tc.id', 'tc.name_zh', 'tc.name', 'tc.card_type', 'tc.suit_id', 's.name_zh')
            ->orderByDesc('selected_count')
            ->orderByDesc('hit_count')
            ->limit(10)
            ->get()
            ->map(fn ($r) => [
                'card_id' => (int) $r->card_id,
                'name_zh' => $r->name_zh,
                'name' => $r->name,
                'card_type' => $r->card_type,
                'suit_id' => $r->suit_id !== null ? (int) $r->suit_id : null,
                'suit_name_zh' => $r->suit_name_zh,
                'selected_count' => (int) $r->selected_count,
                'hit_count' => (int) $r->hit_count,
            ])->values()->toArray();

        // 5) 關鍵字隨時間變化（已點選標籤）
        $trendRows = DB::table('spread_readings as sr')
            ->join('spread_cards as sc', 'sr.id', '=', 'sc.spread_reading_id')
            ->join('spread_card_tag_selections as sts', 'sc.id', '=', 'sts.spread_card_id')
            ->join('tags as t', 'sts.tag_id', '=', 't.id')
            ->where('sr.user_id', $userId)
            ->whereDate('sr.reading_date', '>=', $since)
            ->select(
                DB::raw('DATE(sr.reading_date) as day'),
                't.id as tag_id',
                't.name_zh',
                't.name',
                't.color'
            )
            ->selectRaw('COUNT(*) as count')
            ->groupBy(DB::raw('DATE(sr.reading_date)'), 't.id', 't.name_zh', 't.name', 't.color')
            ->get();

        $totals = [];
        $tagMeta = [];
        foreach ($trendRows as $row) {
            $tagId = (int) $row->tag_id;
            $cnt = (int) $row->count;
            $totals[$tagId] = ($totals[$tagId] ?? 0) + $cnt;
            if (!isset($tagMeta[$tagId])) {
                $tagMeta[$tagId] = [
                    'tag_id' => $tagId,
                    'name_zh' => $row->name_zh,
                    'name' => $row->name,
                    'color' => $row->color,
                ];
            }
        }
        arsort($totals, SORT_NUMERIC);
        $topTrendTagIds = array_slice(array_keys($totals), 0, 5);

        $dateLabels = [];
        $today = Carbon::now()->startOfDay();
        for ($i = $days - 1; $i >= 0; $i--) {
            $dateLabels[] = $today->copy()->subDays($i)->toDateString();
        }

        $countMap = [];
        foreach ($trendRows as $row) {
            $tagId = (int) $row->tag_id;
            if (!in_array($tagId, $topTrendTagIds, true)) {
                continue;
            }
            $day = (string) $row->day;
            $countMap[$tagId][$day] = (int) $row->count;
        }
        $trendSeries = [];
        foreach ($topTrendTagIds as $tagId) {
            $seriesData = [];
            foreach ($dateLabels as $day) {
                $seriesData[] = (int) ($countMap[$tagId][$day] ?? 0);
            }
            $meta = $tagMeta[$tagId] ?? ['name_zh' => '', 'name' => '', 'color' => null];
            $trendSeries[] = [
                'tag_id' => $tagId,
                'name_zh' => $meta['name_zh'],
                'name' => $meta['name'],
                'color' => $meta['color'],
                'total' => (int) ($totals[$tagId] ?? 0),
                'data' => $seriesData,
            ];
        }

        return $this->successResponse([
            'days' => $days,
            'orientation' => $orientation,
            'top_cards' => $topCards,
            'arcana_suit_distribution' => $distribution,
            'selected_cards_ranking' => $selectedCards,
            'keyword_trend' => [
                'date_labels' => $dateLabels,
                'series' => $trendSeries,
            ],
        ], '取得分析儀表板成功');
    }

    /**
     * TOP3 KEYWORDs - Rank of the past N days.
     * 統計過去 N 天內，抽牌出現的關鍵字（標籤）次數，回傳前 3 名。
     *
     * GET /api/analysis/top-keywords?days=30
     *
     * @param Request $request days 可選，預設 30，範圍 1～365
     * @return JsonResponse
     */
    public function topKeywords(Request $request): JsonResponse
    {
        $userId = $request->user()->id;
        $days = (int) $request->input('days', 30);
        $days = max(1, min(365, $days));
        $since = now()->subDays($days)->startOfDay();

        $rows = DB::table('spread_readings as sr')
            ->join('spread_cards as sc', 'sr.id', '=', 'sc.spread_reading_id')
            ->join('card_tags as ct', 'sc.card_id', '=', 'ct.card_id')
            ->join('tags as t', 'ct.tag_id', '=', 't.id')
            ->where('sr.user_id', $userId)
            ->whereDate('sr.reading_date', '>=', $since)
            ->where('ct.is_default', true)
            ->whereNull('ct.user_id')
            ->select(
                't.id as tag_id',
                't.name_zh',
                't.name',
                't.color',
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('t.id', 't.name_zh', 't.name', 't.color')
            ->orderByDesc('count')
            ->limit(50)
            ->get();

        // 依 count 分組，同次數並列；取前 3 組；每組回傳 { count, items }
        $byCount = [];
        foreach ($rows as $row) {
            $count = (int) $row->count;
            if (!isset($byCount[$count])) {
                $byCount[$count] = [];
            }
            $byCount[$count][] = [
                'tag_id' => (int) $row->tag_id,
                'name_zh' => $row->name_zh,
                'name' => $row->name,
                'color' => $row->color,
                'count' => $count,
            ];
        }
        krsort($byCount, SORT_NUMERIC);
        $slice = array_slice(array_values($byCount), 0, 3);
        $groups = array_map(function (array $items) {
            $count = isset($items[0]['count']) ? (int) $items[0]['count'] : 0;
            return ['count' => $count, 'items' => $items];
        }, $slice);

        return $this->successResponse([
            'groups' => $groups,
            'days' => $days,
        ], '取得過去 ' . $days . ' 天 TOP3 關鍵字成功');
    }
}
