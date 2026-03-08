<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AnalysisController extends Controller
{
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
