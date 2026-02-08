<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\TarotCardIndexRequest;
use App\Http\Requests\TarotCardSearchRequest;
use App\Http\Resources\TarotCardResource;
use App\Services\TarotCardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
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
     * @return AnonymousResourceCollection
     */

     #[QueryParameter('per_page', 'integer', '每頁顯示的資料筆數', example: 15, required: false)]
     #[QueryParameter('card_type', 'string', '卡片類型：major 或 minor', example: 'major', required: false)]
     #[QueryParameter('suit_id', 'integer', '花色 ID', example: 1, required: false)]
     #[QueryParameter('search', 'string', '搜尋關鍵字', example: '愚者', required: false)]

    public function index(TarotCardIndexRequest $request): AnonymousResourceCollection
    {
        $params = [
            'per_page' => $request->input('per_page', 15),
            'card_type' => $request->input('card_type'),
            'suit_id' => $request->input('suit_id'),
            'search' => $request->input('search'),
        ];

        $cards = $this->service->getCards($params);

        return TarotCardResource::collection($cards);
    }

    /**
     * 取得單張塔羅牌詳細資訊
     * 
     * 根據塔羅牌 ID 取得完整的牌資訊，包含花色、牌義、標籤等。
     *
     * @param int $id 塔羅牌 ID
     * @return JsonResponse|TarotCardResource
     */
    #[PathParameter('id', 'integer', '塔羅牌 ID', example: 1)]
    public function show(int $id): JsonResponse|TarotCardResource
    {
        $card = $this->service->getCardById($id);

        if (!$card) {
            return response()->json([
                'success' => false,
                'message' => '找不到此塔羅牌',
            ], 404);
        }

        return new TarotCardResource($card);
    }

    /**
     * 根據卡片類型取得塔羅牌
     * 
     * 取得指定類型的所有塔羅牌。支援分頁功能。
     *
     * @param Request $request
     * @param string $type 卡片類型：major（大牌）或 minor（小牌）
     * @return AnonymousResourceCollection|JsonResponse
     */
    #[PathParameter('type', 'string', '卡片類型：major（大牌）或 minor（小牌）', example: 'major')]
    #[QueryParameter('per_page', 'integer', '每頁顯示的資料筆數', example: 15)]
    public function byType(Request $request, string $type): AnonymousResourceCollection|JsonResponse
    {
        try {
            $perPage = $request->input('per_page', 15);
            $cards = $this->service->getCardsByType($type, $perPage);

            return TarotCardResource::collection($cards);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * 根據花色取得塔羅牌
     * 
     * 取得指定花色的所有小牌。支援分頁功能。
     *
     * @param Request $request
     * @param int $suitId 花色 ID（1: 權杖, 2: 聖杯, 3: 寶劍, 4: 錢幣）
     * @return AnonymousResourceCollection
     */
    #[PathParameter('suitId', 'integer', '花色 ID（1: 權杖, 2: 聖杯, 3: 寶劍, 4: 錢幣）', example: 1)]
    #[QueryParameter('per_page', 'integer', '每頁顯示的資料筆數', example: 15)]
    public function bySuit(Request $request, int $suitId): AnonymousResourceCollection
    {
        $perPage = $request->input('per_page', 15);
        $cards = $this->service->getCardsBySuit($suitId, $perPage);

        return TarotCardResource::collection($cards);
    }

    /**
     * 搜尋塔羅牌
     * 
     * 根據關鍵字搜尋塔羅牌的中文或英文名稱。支援模糊搜尋和分頁功能。
     *
     * @param TarotCardSearchRequest $request
     * @return AnonymousResourceCollection
     */
    #[QueryParameter('keyword', 'string', '搜尋關鍵字（中文或英文名稱）', example: '愚者', required: true)]
    #[QueryParameter('per_page', 'integer', '每頁顯示的資料筆數', example: 15)]
    public function search(TarotCardSearchRequest $request): AnonymousResourceCollection
    {
        $keyword = $request->input('keyword');
        $perPage = $request->input('per_page', 15);
        
        $cards = $this->service->searchCards($keyword, $perPage);

        return TarotCardResource::collection($cards);
    }
}

