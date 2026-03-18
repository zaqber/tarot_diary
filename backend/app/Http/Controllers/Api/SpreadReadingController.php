<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\SpreadReadingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SpreadReadingController extends Controller
{
    public function __construct(
        protected SpreadReadingService $service
    ) {}

    /**
     * 建立一筆新的三張牌陣
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'theme' => 'sometimes|string|in:overall,love,career,finance',
        ]);
        $userId = $request->user()->id;
        $theme = (string) $request->input('theme', 'overall');
        $reading = $this->service->create($userId, $theme);
        return $this->successResponse([
            'id' => $reading->id,
            'spread_type_id' => $reading->spread_type_id,
            'theme' => $reading->theme,
            'theme_label_zh' => SpreadReadingService::themeLabel($reading->theme ?? 'overall'),
            'reading_date' => $reading->reading_date?->toDateString(),
        ], '牌陣建立成功', 201);
    }

    /**
     * 為牌陣的某個位置紀錄一張牌（每抽一張就呼叫一次）
     *
     * @param Request $request
     * @param int $id spread_reading_id
     * @return JsonResponse
     */
    public function addCard(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'position_number' => 'required|integer|min:1|max:3',
            'card_id' => 'required|integer|exists:tarot_cards,id',
            'is_reversed' => 'sometimes|boolean',
        ]);
        try {
            $sc = $this->service->addCard(
                $id,
                (int) $request->input('position_number'),
                (int) $request->input('card_id'),
                (bool) $request->input('is_reversed', false)
            );
            return $this->successResponse([
                'spread_card_id' => $sc->id,
                'position_number' => $sc->position_number,
                'card_id' => $sc->card_id,
            ], '已紀錄此張牌');
        } catch (\InvalidArgumentException $e) {
            return $this->errorResponse($e->getMessage(), 422);
        }
    }

    /**
     * 取得牌陣列表或依日期單筆
     * - date=today 或 date=Y-m-d：回傳該日一筆牌陣（完整），無則 404
     * - 無 date：回傳分頁列表（供 history 用）
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $userId = $request->user()->id;
        $date = $request->input('date');

        if ($date !== null && $date !== '') {
            $reading = $this->service->getReadingByDate($userId, $date, $request);
            if ($reading === null) {
                return $this->errorResponse('該日尚無抽牌紀錄', 404);
            }
            return $this->successResponse($reading, '取得當日牌陣成功');
        }

        $perPage = (int) $request->input('per_page', 20);
        $page = (int) $request->input('page', 1);
        $paginator = $this->service->listReadings($userId, $perPage, $page);
        $data = $paginator->getCollection()->map(function ($reading) {
            $theme = $reading->theme ?? 'overall';
            return [
                'id' => $reading->id,
                'reading_date' => $reading->reading_date?->toDateString(),
                'theme' => $theme,
                'theme_label_zh' => SpreadReadingService::themeLabel($theme),
                'spread_cards' => $reading->spreadCards->sortBy('position_number')->map(function ($sc) {
                    return [
                        'position_number' => $sc->position_number,
                        'card_id' => $sc->card_id,
                        'is_reversed' => (bool) $sc->is_reversed,
                        'card' => $sc->card ? [
                            'id' => $sc->card->id,
                            'name' => $sc->card->name,
                            'name_zh' => $sc->card->name_zh,
                        ] : null,
                    ];
                })->values()->toArray(),
            ];
        })->toArray();

        return response()->json([
            'success' => true,
            'message' => '取得牌陣列表成功',
            'data' => $data,
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        ]);
    }

    /**
     * 切換「符合當天狀態」的標籤（點選後紀錄）
     *
     * @param Request $request
     * @param int $id spread_reading_id
     * @param int $position 1, 2, 3
     * @return JsonResponse
     */
    public function toggleTag(Request $request, int $id, int $position): JsonResponse
    {
        $request->validate(['tag_id' => 'required|integer|exists:tags,id']);
        if ($position < 1 || $position > 3) {
            return $this->errorResponse('position 須為 1、2 或 3', 422);
        }
        $result = $this->service->toggleSpreadCardTag($id, $position, (int) $request->input('tag_id'));
        return $this->successResponse($result, '已更新標籤狀態');
    }

    /**
     * 取得牌陣詳情（含每張牌名稱、tags、selected_tag_ids，三張都抽完後顯示 About My Day 用）
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $data = $this->service->getReadingWithCards($id, $request);
        return $this->successResponse($data, '取得牌陣詳情成功');
    }
}
