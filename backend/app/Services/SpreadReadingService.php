<?php

namespace App\Services;

use App\Http\Resources\TarotCardResource;
use App\Models\SpreadCard;
use App\Models\SpreadCardTagSelection;
use App\Models\SpreadReading;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class SpreadReadingService
{
    /** 三張牌陣的 spread_type_id（依 SpreadTypesSeeder） */
    public const THREE_CARD_SPREAD_TYPE_ID = 2;

    /** 抽牌主題 key => 中文顯示 */
    public const THEMES = [
        'overall' => '整體',
        'love' => '感情',
        'career' => '事業',
        'finance' => '財務',
    ];

    public static function themeLabel(string $theme): string
    {
        return self::THEMES[$theme] ?? $theme;
    }

    public static function isValidTheme(string $theme): bool
    {
        return array_key_exists($theme, self::THEMES);
    }

    /**
     * 建立一筆新的牌陣紀錄（三張牌陣）
     *
     * @param int $userId
     * @param string $theme overall|love|career|finance
     * @return SpreadReading
     */
    public function create(int $userId = 1, string $theme = 'overall'): SpreadReading
    {
        $this->ensureUserExists($userId);
        if (! self::isValidTheme($theme)) {
            $theme = 'overall';
        }

        return SpreadReading::create([
            'user_id' => $userId,
            'spread_type_id' => self::THREE_CARD_SPREAD_TYPE_ID,
            'theme' => $theme,
            'reading_date' => now()->toDateString(),
            'question' => null,
            'overall_note' => null,
            'is_reviewed' => false,
        ]);
    }

    /**
     * @deprecated 已改為每日可多次抽牌；建立請改用 {@see create()}。保留供舊程式或手動維運參考。
     *
     * @return array{0: SpreadReading, 1: bool} [reading, wasCreated]
     */
    public function findOrCreateTodayReading(int $userId, string $theme = 'overall'): array
    {
        $this->ensureUserExists($userId);
        if (! self::isValidTheme($theme)) {
            $theme = 'overall';
        }

        $today = now()->toDateString();
        $existing = SpreadReading::query()
            ->where('user_id', $userId)
            ->where('spread_type_id', self::THREE_CARD_SPREAD_TYPE_ID)
            ->whereDate('reading_date', $today)
            ->orderByDesc('id')
            ->first();

        if ($existing) {
            if ($existing->spreadCards()->count() === 0 && ($existing->theme ?? '') !== $theme) {
                $existing->update(['theme' => $theme]);
                $existing->refresh();
            }

            return [$existing, false];
        }

        return [$this->create($userId, $theme), true];
    }

    /**
     * 為牌陣的某個位置紀錄一張牌
     *
     * @param int $spreadReadingId
     * @param int $positionNumber 1, 2, 3
     * @param int $cardId
     * @param bool $isReversed
     * @return SpreadCard
     */
    public function addCard(int $spreadReadingId, int $positionNumber, int $cardId, bool $isReversed = false): SpreadCard
    {
        $reading = SpreadReading::findOrFail($spreadReadingId);
        if ($reading->spread_type_id !== self::THREE_CARD_SPREAD_TYPE_ID) {
            throw new \InvalidArgumentException('此牌陣僅支援三張牌');
        }
        if ($positionNumber < 1 || $positionNumber > 3) {
            throw new \InvalidArgumentException('位置必須為 1、2 或 3');
        }

        $existing = SpreadCard::where('spread_reading_id', $spreadReadingId)
            ->where('position_number', $positionNumber)
            ->first();
        if ($existing) {
            $existing->update(['card_id' => $cardId, 'is_reversed' => $isReversed]);
            return $existing->fresh(['card.suit', 'card.tags']);
        }

        return SpreadCard::create([
            'spread_reading_id' => $spreadReadingId,
            'position_number' => $positionNumber,
            'card_id' => $cardId,
            'is_reversed' => $isReversed,
        ]);
    }

    /**
     * 依日期取得該使用者當天的三張牌陣（一筆）
     *
     * @param int $userId
     * @param string $date Y-m-d 或 'today'
     * @param Request $request
     * @return array|null
     */
    public function getReadingByDate(int $userId, string $date, Request $request): ?array
    {
        $dateStr = ($date === 'today' || $date === '') ? now()->toDateString() : $date;
        $reading = SpreadReading::with(['spreadType', 'spreadCards.tagSelections', 'spreadCards.card.suit', 'spreadCards.card.tags'])
            ->where('user_id', $userId)
            ->where('spread_type_id', self::THREE_CARD_SPREAD_TYPE_ID)
            ->whereDate('reading_date', $dateStr)
            ->orderByDesc('id')
            ->first();
        if (!$reading) {
            return null;
        }
        return $this->formatReadingWithCards($reading, $request);
    }

    /**
     * 列出使用者的牌陣紀錄（分頁，供 history 用）
     *
     * @param int $userId
     * @param int $perPage
     * @param int $page
     * @return LengthAwarePaginator
     */
    public function listReadings(int $userId, int $perPage = 20, int $page = 1): LengthAwarePaginator
    {
        return SpreadReading::with(['spreadCards.card:id,name,name_zh'])
            ->where('user_id', $userId)
            ->where('spread_type_id', self::THREE_CARD_SPREAD_TYPE_ID)
            ->orderByDesc('reading_date')
            ->orderByDesc('id')
            ->paginate($perPage, ['*'], 'page', $page);
    }

    /**
     * 取得牌陣詳情（含每張牌完整資訊：名稱、tags）
     *
     * @param int $id
     * @param Request $request 用於 TarotCardResource 的 userId
     * @return array
     */
    public function getReadingWithCards(int $id, Request $request): array
    {
        $reading = SpreadReading::with(['spreadType', 'spreadCards.tagSelections', 'spreadCards.card.suit', 'spreadCards.card.tags'])
            ->where('user_id', $request->user()->id)
            ->findOrFail($id);

        return $this->formatReadingWithCards($reading, $request);
    }

    /**
     * 切換「符合當天狀態」的標籤（點選後紀錄）
     *
     * @param int $spreadReadingId
     * @param int $positionNumber 1, 2, 3
     * @param int $tagId
     * @return array ['selected' => bool, 'selected_tag_ids' => int[]]
     */
    public function toggleSpreadCardTag(int $userId, int $spreadReadingId, int $positionNumber, int $tagId): array
    {
        $reading = SpreadReading::where('id', $spreadReadingId)
            ->where('user_id', $userId)
            ->firstOrFail();

        $spreadCard = SpreadCard::where('spread_reading_id', $reading->id)
            ->where('position_number', $positionNumber)
            ->firstOrFail();
        $existing = SpreadCardTagSelection::where('spread_card_id', $spreadCard->id)
            ->where('tag_id', $tagId)
            ->first();
        if ($existing) {
            $existing->delete();
            $selected = false;
        } else {
            SpreadCardTagSelection::create([
                'spread_card_id' => $spreadCard->id,
                'tag_id' => $tagId,
            ]);
            $selected = true;
        }
        $selectedTagIds = SpreadCardTagSelection::where('spread_card_id', $spreadCard->id)
            ->pluck('tag_id')
            ->toArray();
        return ['selected' => $selected, 'selected_tag_ids' => $selectedTagIds];
    }

    /**
     * @param SpreadReading $reading
     * @param Request $request
     * @return array
     */
    private function formatReadingWithCards(SpreadReading $reading, Request $request): array
    {
        $cardsData = $reading->spreadCards->map(function (SpreadCard $sc) use ($request) {
            $card = $sc->card;
            if ($card) {
                $card->loadMissing(['tags']);
            }
            $resource = new TarotCardResource($card);
            $selectedTagIds = $sc->tagSelections ? $sc->tagSelections->pluck('tag_id')->toArray() : [];
            return [
                'position_number' => $sc->position_number,
                'spread_card_id' => $sc->id,
                'card_id' => $sc->card_id,
                'is_reversed' => $sc->is_reversed,
                'selected_tag_ids' => $selectedTagIds,
                'card' => $resource->toArray($request),
            ];
        })->toArray();

        $theme = $reading->theme ?? 'overall';

        return [
            'id' => $reading->id,
            'user_id' => $reading->user_id,
            'spread_type_id' => $reading->spread_type_id,
            'theme' => $theme,
            'theme_label_zh' => self::themeLabel($theme),
            'spread_type' => $reading->spreadType ? [
                'id' => $reading->spreadType->id,
                'name' => $reading->spreadType->name,
                'name_zh' => $reading->spreadType->name_zh,
                'card_count' => $reading->spreadType->card_count,
            ] : null,
            'reading_date' => $reading->reading_date?->toDateString(),
            'reading_time' => $reading->reading_time?->toIso8601String(),
            'question' => $reading->question,
            'overall_note' => $reading->overall_note,
            'is_reviewed' => $reading->is_reviewed,
            'ai_question' => $reading->ai_question,
            'ai_interpretation' => $reading->ai_interpretation,
            'ai_generated_at' => $reading->ai_generated_at?->toIso8601String(),
            'spread_cards' => $cardsData,
        ];
    }

    /**
     * 確保用戶存在，不存在則建立預設用戶（與 TarotCardService 一致）
     *
     * @param int $userId
     * @return void
     */
    private function ensureUserExists(int $userId): void
    {
        if (DB::table('users')->where('id', $userId)->exists()) {
            return;
        }
        DB::table('users')->insert([
            'id' => $userId,
            'username' => 'default_user_' . $userId,
            'email' => 'user' . $userId . '@example.com',
            'password_hash' => bcrypt('password'),
            'display_name' => '預設用戶 ' . $userId,
            'timezone' => 'Asia/Taipei',
            'morning_reminder_time' => '08:00:00',
            'evening_reminder_time' => '20:00:00',
            'is_morning_reminder_enabled' => true,
            'is_evening_reminder_enabled' => true,
            'is_active' => true,
            'created_at' => now(),
        ]);
    }
}
