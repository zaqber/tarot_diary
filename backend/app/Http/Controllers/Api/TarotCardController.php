<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\TarotCardDeleteTagsRequest;
use App\Http\Requests\TarotCardIndexRequest;
use App\Http\Requests\TarotCardSetTagsRequest;
use App\Http\Resources\TarotCardResource;
use App\Services\TarotCardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Dedoc\Scramble\Attributes\QueryParameter;
use Dedoc\Scramble\Attributes\PathParameter;

class TarotCardController extends Controller
{
    protected TarotCardService $service;

    public function __construct(TarotCardService $service)
    {
        $this->service = $service;
    }

    /**
     * 取得塔羅牌列表
     *
     * 支援分頁、篩選和搜尋功能。可以根據卡片類型、花色、關鍵字進行篩選。
     *
     * @param TarotCardIndexRequest $request
     * @return JsonResponse
     */

     #[QueryParameter('per_page', 'integer', '每頁顯示的資料筆數', example: 15, required: false)]
     #[QueryParameter('card_type', 'string', '卡片類型：major 或 minor', example: 'major', required: false)]
     #[QueryParameter('suit_id', 'integer', '花色 ID', example: 1, required: false)]
     #[QueryParameter('search', 'string', '搜尋關鍵字', example: '愚者', required: false)]

    public function index(TarotCardIndexRequest $request): JsonResponse
    {
        $params = [
            'per_page' => $request->input('per_page', 15),
            'card_type' => $request->input('card_type'),
            'suit_id' => $request->input('suit_id'),
            'search' => $request->input('search'),
        ];

        $cards = $this->service->getCards($params);

        return response()->json([
            'success' => true,
            'message' => '取得塔羅牌列表成功',
            'data' => TarotCardResource::collection($cards),
            'meta' => [
                'current_page' => $cards->currentPage(),
                'last_page' => $cards->lastPage(),
                'per_page' => $cards->perPage(),
                'total' => $cards->total(),
            ],
        ]);
    }

    /**
     * 隨機取得 N 張塔羅牌（用於自動抽牌）
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function random(Request $request): JsonResponse
    {
        $count = min(max((int) $request->input('count', 3), 1), 10);
        $cards = $this->service->getRandomCards($count);
        return $this->successResponse(
            TarotCardResource::collection($cards),
            '隨機抽牌成功'
        );
    }

    /**
     * 取得單張塔羅牌詳細資訊
     *
     * 根據塔羅牌 ID 取得完整的牌資訊，包含花色、牌義、標籤等。
     *
     * @param int $id 塔羅牌 ID
     * @return JsonResponse
     */
    #[PathParameter('id', 'integer', '塔羅牌 ID', example: 1)]
    public function show(int $id): JsonResponse
    {
        $card = $this->service->getCardById($id);

        if (!$card) {
            return $this->errorResponse('找不到此塔羅牌', 404);
        }

        return $this->successResponse(
            new TarotCardResource($card),
            '取得塔羅牌詳細資訊成功'
        );
    }

    // 以下方法目前未在路由中註冊，暫時註釋
    // 如需使用，請先在 Service 中實現對應方法並添加路由

    // /**
    //  * 根據卡片類型取得塔羅牌
    //  *
    //  * 取得指定類型的所有塔羅牌。支援分頁功能。
    //  *
    //  * @param Request $request
    //  * @param string $type 卡片類型：major（大牌）或 minor（小牌）
    //  * @return JsonResponse
    //  */
    // #[PathParameter('type', 'string', '卡片類型：major（大牌）或 minor（小牌）', example: 'major')]
    // #[QueryParameter('per_page', 'integer', '每頁顯示的資料筆數', example: 15)]
    // public function byType(Request $request, string $type): JsonResponse
    // {
    //     try {
    //         $perPage = $request->input('per_page', 15);
    //         $cards = $this->service->getCardsByType($type, $perPage);
    //
    //         return $this->successResponse(
    //             TarotCardResource::collection($cards),
    //             '取得塔羅牌列表成功'
    //         );
    //     } catch (\InvalidArgumentException $e) {
    //         return $this->errorResponse($e->getMessage(), 400);
    //     }
    // }

    // /**
    //  * 根據花色取得塔羅牌
    //  *
    //  * 取得指定花色的所有小牌。支援分頁功能。
    //  *
    //  * @param Request $request
    //  * @param int $suitId 花色 ID（1: 權杖, 2: 聖杯, 3: 寶劍, 4: 錢幣）
    //  * @return JsonResponse
    //  */
    // #[PathParameter('suitId', 'integer', '花色 ID（1: 權杖, 2: 聖杯, 3: 寶劍, 4: 錢幣）', example: 1)]
    // #[QueryParameter('per_page', 'integer', '每頁顯示的資料筆數', example: 15)]
    // public function bySuit(Request $request, int $suitId): JsonResponse
    // {
    //     $perPage = $request->input('per_page', 15);
    //     $cards = $this->service->getCardsBySuit($suitId, $perPage);
    //
    //     return $this->successResponse(
    //         TarotCardResource::collection($cards),
    //         '取得塔羅牌列表成功'
    //     );
    // }

    // /**
    //  * 搜尋塔羅牌
    //  *
    //  * 根據關鍵字搜尋塔羅牌的中文或英文名稱。支援模糊搜尋和分頁功能。
    //  *
    //  * @param TarotCardSearchRequest $request
    //  * @return JsonResponse
    //  */
    // #[QueryParameter('keyword', 'string', '搜尋關鍵字（中文或英文名稱）', example: '愚者', required: true)]
    // #[QueryParameter('per_page', 'integer', '每頁顯示的資料筆數', example: 15)]
    // public function search(TarotCardSearchRequest $request): JsonResponse
    // {
    //     $keyword = $request->input('keyword');
    //     $perPage = $request->input('per_page', 15);
    //
    //     $cards = $this->service->searchCards($keyword, $perPage);
    //
    //     return $this->successResponse(
    //         TarotCardResource::collection($cards),
    //         '搜尋塔羅牌成功'
    //     );
    // }

    /**
     * 取得牌的有效標籤
     *
     * 回傳使用者實際看到的標籤 = 預設標籤（排除隱藏）+ 自訂標籤。
     *
     * @param Request $request
     * @param int $id 塔羅牌 ID
     * @return JsonResponse
     */
    #[PathParameter('id', 'integer', '塔羅牌 ID', example: 1)]
    public function getActiveTags(Request $request, int $id): JsonResponse
    {
        try {
            $userId = $request->user()?->id ?? 1;
            $tags = $this->service->getActiveTags($id, $userId);

            return $this->successResponse($tags, '取得有效標籤成功');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->errorResponse('找不到此塔羅牌', 404);
        } catch (\Exception $e) {
            return $this->errorResponse('取得標籤時發生錯誤：' . $e->getMessage(), 500);
        }
    }

    /**
     * 取得牌的系統預設標籤
     *
     * 取得指定牌的系統預設標籤列表。
     *
     * @param int $id 塔羅牌 ID
     * @return JsonResponse
     */
    #[PathParameter('id', 'integer', '塔羅牌 ID', example: 1)]
    public function getDefaultTags(int $id): JsonResponse
    {
        try {
            $tags = $this->service->getDefaultTags($id);

            return $this->successResponse($tags, '取得系統預設標籤成功');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->errorResponse('找不到此塔羅牌', 404);
        } catch (\Exception $e) {
            return $this->errorResponse('取得標籤時發生錯誤：' . $e->getMessage(), 500);
        }
    }

    /**
     * 取得牌的用戶自訂標籤
     *
     * 取得指定牌的用戶自訂標籤列表。
     *
     * @param Request $request
     * @param int $id 塔羅牌 ID
     * @return JsonResponse
     */
    #[PathParameter('id', 'integer', '塔羅牌 ID', example: 1)]
    public function getCustomTags(Request $request, int $id): JsonResponse
    {
        try {
            // 暫時使用預設 user_id = 1（尚未實作權限系統）
            $userId = $request->user()?->id ?? 1;

            $tags = $this->service->getCustomTags($id, $userId);

            return $this->successResponse($tags, '取得用戶自訂標籤成功');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->errorResponse('找不到此塔羅牌', 404);
        } catch (\Exception $e) {
            return $this->errorResponse('取得標籤時發生錯誤：' . $e->getMessage(), 500);
        }
    }

    /**
     * 添加用戶自訂標籤
     *
     * 為指定牌添加用戶自訂標籤（增量添加，不會清除現有標籤）。
     * 後端自動判斷標籤是否存在，不存在則自動創建。
     *
     * 統一格式：
     * {"name": "tag_name", "name_zh": "標籤名稱", "position": "upright"}
     * 或只提供中文名稱：{"name_zh": "標籤名稱", "position": "upright"}
     *
     * @param TarotCardSetTagsRequest $request
     * @param int $id 塔羅牌 ID
     * @return JsonResponse
     */
    #[PathParameter('id', 'integer', '塔羅牌 ID', example: 1)]
    public function setCustomTags(TarotCardSetTagsRequest $request, int $id): JsonResponse
    {
        try {
            // 暫時使用預設 user_id = 1（尚未實作權限系統）
            $userId = $request->user()?->id ?? 1;

            $tags = $request->input('tags');
            $result = $this->service->setCustomTags($id, $userId, $tags);

            return $this->successResponse($result, '標籤添加成功');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->errorResponse('找不到此塔羅牌', 404);
        } catch (\Exception $e) {
            return $this->errorResponse('設定標籤時發生錯誤：' . $e->getMessage(), 500);
        }
    }

    /**
     * 刪除用戶自訂標籤
     *
     * 刪除指定牌的用戶自訂標籤。可以通過 tag_id 或 tag 名稱來指定要刪除的標籤。
     *
     * @param TarotCardDeleteTagsRequest $request
     * @param int $id 塔羅牌 ID
     * @return JsonResponse
     */
    #[PathParameter('id', 'integer', '塔羅牌 ID', example: 1)]
    public function deleteCustomTags(TarotCardDeleteTagsRequest $request, int $id): JsonResponse
    {
        try {
            // 暫時使用預設 user_id = 1（尚未實作權限系統）
            $userId = $request->user()?->id ?? 1;

            $tags = $request->input('tags');
            $result = $this->service->deleteCustomTags($id, $userId, $tags);

            return $this->successResponse($result, '標籤刪除成功');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->errorResponse('找不到此塔羅牌', 404);
        } catch (\RuntimeException $e) {
            return $this->errorResponse($e->getMessage(), 422);
        } catch (\Exception $e) {
            return $this->errorResponse('刪除標籤時發生錯誤：' . $e->getMessage(), 500);
        }
    }

    /**
     * 重設回系統預設標籤
     *
     * 刪除指定牌的所有用戶自訂標籤，恢復為系統預設標籤。
     *
     * @param Request $request
     * @param int $id 塔羅牌 ID
     * @return JsonResponse
     */
    #[PathParameter('id', 'integer', '塔羅牌 ID', example: 1)]
    public function resetToDefaultTags(Request $request, int $id): JsonResponse
    {
        try {
            // 暫時使用預設 user_id = 1（尚未實作權限系統）
            $userId = $request->user()?->id ?? 1;

            $tags = $this->service->resetToDefaultTags($id, $userId);

            return $this->successResponse($tags, '已重設為系統預設標籤');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->errorResponse('找不到此塔羅牌', 404);
        } catch (\Exception $e) {
            return $this->errorResponse('重設標籤時發生錯誤：' . $e->getMessage(), 500);
        }
    }
}
