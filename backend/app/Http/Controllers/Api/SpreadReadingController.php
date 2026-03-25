<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SpreadReading;
use App\Services\SpreadReadingService;
use App\Services\TarotInterpretationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SpreadReadingController extends Controller
{
    public function __construct(
        protected SpreadReadingService $service,
        protected TarotInterpretationService $tarotAi
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
        $payload = [
            'id' => $reading->id,
            'spread_type_id' => $reading->spread_type_id,
            'theme' => $reading->theme,
            'theme_label_zh' => SpreadReadingService::themeLabel($reading->theme ?? 'overall'),
            'reading_date' => $reading->reading_date?->toDateString(),
            'reading_time' => $reading->reading_time?->toIso8601String(),
        ];

        return $this->successResponse(
            $payload,
            '牌陣建立成功',
            201
        );
    }

    /**
     * 為牌陣的某個位置紀錄一張牌（每抽一張就呼叫一次）
     *
     * @param Request $request
     * @param int $id spread_reading_id
     * @return JsonResponse
     */
    /**
     * 尚未抽任何牌前可變更主題
     */
    public function updateTheme(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'theme' => 'required|string|in:overall,love,career,finance',
        ]);
        $reading = SpreadReading::where('user_id', $request->user()->id)->findOrFail($id);
        if ($reading->spreadCards()->count() > 0) {
            return $this->errorResponse('已有抽牌紀錄後無法變更主題', 422);
        }
        $reading->update(['theme' => $request->input('theme')]);

        return $this->successResponse([
            'theme' => $reading->theme,
            'theme_label_zh' => SpreadReadingService::themeLabel($reading->theme ?? 'overall'),
        ], '主題已更新');
    }

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
                'reading_time' => $reading->reading_time?->toIso8601String(),
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
        $result = $this->service->toggleSpreadCardTag(
            $request->user()->id,
            $id,
            $position,
            (int) $request->input('tag_id')
        );
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

    /**
     * 以 AI（預設 Gemini）依主題＋三張牌與選填提問解牌，寫入 ai_question / ai_interpretation（AI_PROVIDER）
     */
    public function requestAiInterpret(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'topic' => 'nullable|string|max:120',
            'question' => 'nullable|string|max:2000',
        ]);

        $reading = SpreadReading::where('user_id', $request->user()->id)
            ->with(['spreadCards.card'])
            ->findOrFail($id);

        if ($reading->spreadCards->count() !== 3) {
            return $this->errorResponse('須抽滿三張牌後才能請 AI 解牌', 422);
        }

        $themeLabel = SpreadReadingService::themeLabel($reading->theme ?? 'overall');
        $positionLabels = [
            1 => '過去（根源與背景）',
            2 => '現在（當前處境）',
            3 => '未來（走向與可能發展）',
        ];

        $lines = [];
        foreach ($reading->spreadCards->sortBy('position_number') as $sc) {
            $card = $sc->card;
            $pos = $positionLabels[$sc->position_number] ?? '第 '.$sc->position_number.' 張';
            $ori = $sc->is_reversed ? '逆位' : '正位';
            $name = $card ? ($card->name_zh.'（'.$card->name.'）') : '未知';
            $brief = $sc->is_reversed
                ? (string) ($card->official_meaning_reversed ?? '')
                : (string) ($card->official_meaning_upright ?? '');
            $brief = trim($brief) !== '' ? ' 參考牌義：'.$brief : '';
            $lines[] = "- **{$pos}**：{$name}，{$ori}。{$brief}";
        }
        $cardsBlock = implode("\n", $lines);

        $optionalTopic = trim((string) $request->input('topic', ''));
        $optionalQ = trim((string) $request->input('question', ''));
        $topicPart = $optionalTopic !== ''
            ? "\n\n【使用者希望聚焦的主題】\n".$optionalTopic."\n請優先圍繞這個主題做解讀。"
            : '';
        $qPart = $optionalQ !== ''
            ? "\n\n【使用者特別想問的問題】\n".$optionalQ."\n請在解牌中充分回應此問題。"
            : '';

        $prompt = <<<PROMPT
你是一位溫暖、具同理心的塔羅解牌師，請全程使用繁體中文回答。

請依下列「抽牌主題」與三張牌（含正逆位與牌陣位置：過去／現在／未來）給出整合解讀，包含：整體氛圍、各位置簡述、三張牌如何連貫、具體可行的建議。語氣支持性，避免恐嚇式或宿命論預言；可提醒塔羅作為自我反思工具，最終決定仍在使用者手中。

**抽牌主題**：{$themeLabel}

**牌面**：
{$cardsBlock}
{$topicPart}
{$qPart}

請分段說明（可使用 ## 或小標題），內容精簡、重點導向：
- 全文約 180～260 字
- 不超過 5 段
- 每段 1～3 句，避免冗長
PROMPT;

        try {
            $text = $this->tarotAi->interpret($prompt);
        } catch (\RuntimeException $e) {
            return $this->errorResponse($e->getMessage(), 503);
        }

        $storedPrompt = null;
        if ($optionalTopic !== '' && $optionalQ !== '') {
            $storedPrompt = "主題：{$optionalTopic}\n問題：{$optionalQ}";
        } elseif ($optionalTopic !== '') {
            $storedPrompt = "主題：{$optionalTopic}";
        } elseif ($optionalQ !== '') {
            $storedPrompt = $optionalQ;
        }

        $reading->update([
            'ai_question' => $storedPrompt,
            'ai_interpretation' => $text,
            'ai_generated_at' => now(),
        ]);

        $data = $this->service->getReadingWithCards($id, $request);

        return $this->successResponse($data, 'AI 解牌完成');
    }
}
