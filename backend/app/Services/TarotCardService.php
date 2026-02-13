<?php

namespace App\Services;

use App\Models\Tag;
use App\Models\TarotCard;
use App\Repositories\TarotCardRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class TarotCardService
{
    protected TarotCardRepositoryInterface $repository;

    public function __construct(TarotCardRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * 取得塔羅牌列表
     *
     * @param array $params 查詢參數
     * @return LengthAwarePaginator
     */
    public function getCards(array $params = []): LengthAwarePaginator
    {
        $perPage = $params['per_page'] ?? 15;
        $filters = [
            'card_type' => $params['card_type'] ?? null,
            'suit_id' => $params['suit_id'] ?? null,
            'search' => $params['search'] ?? null,
        ];

        // 移除空值
        $filters = array_filter($filters, fn($value) => !is_null($value));

        return $this->repository->getAllPaginated($perPage, $filters);
    }

    /**
     * 取得單張塔羅牌詳細資訊
     *
     * @param int $id
     * @return \App\Models\TarotCard|null
     */
    public function getCardById(int $id)
    {
        return $this->repository->findById($id);
    }

    /**
     * 隨機取得 N 張不重複的塔羅牌（用於自動抽牌）
     *
     * @param int $count
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getRandomCards(int $count = 3): \Illuminate\Database\Eloquent\Collection
    {
        return $this->repository->getRandom($count);
    }

    /**
     * 取得牌的系統預設標籤
     *
     * @param int $cardId
     * @return array
     */
    public function getDefaultTags(int $cardId): array
    {
        $card = TarotCard::findOrFail($cardId);
        
        return $card->defaultTags()->get()->map(function ($tag) {
            return [
                'id' => $tag->id,
                'name' => $tag->name,
                'name_zh' => $tag->name_zh,
                'position' => $tag->pivot->position,
            ];
        })->toArray();
    }

    /**
     * 取得牌的用戶自訂標籤
     *
     * @param int $cardId
     * @param int $userId
     * @return array
     */
    public function getCustomTags(int $cardId, int $userId): array
    {
        $card = TarotCard::findOrFail($cardId);
        
        // 確保用戶存在
        $this->ensureUserExists($userId);
        
        return $card->customTags($userId)->get()->map(function ($tag) {
            return [
                'id' => $tag->id,
                'name' => $tag->name,
                'name_zh' => $tag->name_zh,
                'position' => $tag->pivot->position,
            ];
        })->toArray();
    }

    /**
     * 確保用戶存在，不存在則創建預設用戶
     *
     * @param int $userId
     * @return void
     */
    protected function ensureUserExists(int $userId): void
    {
        if (!DB::table('users')->where('id', $userId)->exists()) {
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

    /**
     * 添加用戶自訂標籤（增量添加，不刪除現有標籤）
     * 後端自動判斷標籤是否存在，不存在則自動創建
     *
     * @param int $cardId
     * @param int $userId
     * @param array $tags 格式: [
     *   ['name' => 'tag_name', 'name_zh' => '標籤名稱', 'position' => 'upright'],
     *   ['name_zh' => '標籤名稱', 'position' => 'upright'],  // 只有中文名稱也可以
     *   ...
     * ]
     * @return array
     */
    public function setCustomTags(int $cardId, int $userId, array $tags): array
    {
        $card = TarotCard::findOrFail($cardId);
        
        // 確保用戶存在
        $this->ensureUserExists($userId);

        DB::beginTransaction();
        try {
            $insertData = [];
            
            foreach ($tags as $tag) {
                if (!isset($tag['position'])) {
                    continue;
                }

                $position = $tag['position'];
                if (!in_array($position, ['upright', 'reversed', 'both'])) {
                    continue;
                }

                // 後端自動查找或創建標籤
                $tagId = $this->findOrCreateTag($tag);

                if (!$tagId) {
                    continue;
                }

                // 檢查該用戶是否已經有這個標籤（相同 position）
                $existing = DB::table('card_tags')
                    ->where('card_id', $cardId)
                    ->where('tag_id', $tagId)
                    ->where('position', $position)
                    ->where('user_id', $userId)
                    ->where('is_default', false)
                    ->exists();

                // 如果不存在，才添加
                if (!$existing) {
                    $insertData[] = [
                        'card_id' => $cardId,
                        'tag_id' => $tagId,
                        'position' => $position,
                        'is_default' => false,
                        'user_id' => $userId,
                    ];
                }
            }

            // 批量插入新標籤
            if (!empty($insertData)) {
                DB::table('card_tags')->insert($insertData);
            }

            DB::commit();

            // 重新載入關聯並返回更新後的所有自訂標籤
            $card->load('tags');
            return $card->customTags($userId)->get()->map(function ($tag) {
                return [
                    'id' => $tag->id,
                    'name' => $tag->name,
                    'name_zh' => $tag->name_zh,
                    'position' => $tag->pivot->position,
                ];
            })->toArray();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * 查找標籤（不創建）
     *
     * @param array $tagData 包含 name 或 name_zh 的陣列
     * @return int|null 返回 tag_id，找不到則返回 null
     */
    protected function findTag(array $tagData): ?int
    {
        $name = $tagData['name'] ?? null;
        $nameZh = $tagData['name_zh'] ?? null;

        if (!$name && !$nameZh) {
            return null;
        }

        // 先嘗試通過中文名稱查找
        if ($nameZh) {
            $tag = Tag::where('name_zh', $nameZh)->first();
            if ($tag) {
                return $tag->id;
            }
        }

        // 再嘗試通過英文名稱查找
        if ($name) {
            $tag = Tag::where('name', $name)->first();
            if ($tag) {
                return $tag->id;
            }
        }

        return null;
    }

    /**
     * 查找或創建標籤
     *
     * @param array $tagData 包含 name 或 name_zh 的陣列
     * @return int|null 返回 tag_id
     */
    protected function findOrCreateTag(array $tagData): ?int
    {
        $name = $tagData['name'] ?? null;
        $nameZh = $tagData['name_zh'] ?? null;

        if (!$name && !$nameZh) {
            return null;
        }

        // 先嘗試通過中文名稱查找
        if ($nameZh) {
            $tag = Tag::where('name_zh', $nameZh)->first();
            if ($tag) {
                return $tag->id;
            }
        }

        // 再嘗試通過英文名稱查找
        if ($name) {
            $tag = Tag::where('name', $name)->first();
            if ($tag) {
                // 如果找到但沒有中文名稱，更新它
                if ($nameZh && empty($tag->name_zh)) {
                    $tag->update(['name_zh' => $nameZh]);
                }
                return $tag->id;
            }
        }

        // 如果都不存在，創建新標籤
        $tagDataToInsert = [];
        
        if ($name) {
            $tagDataToInsert['name'] = $name;
        } else {
            // 如果沒有英文名稱，使用中文名稱的拼音轉換（簡化處理）
            $tagDataToInsert['name'] = strtolower(preg_replace('/\s+/', '_', $nameZh));
        }
        
        if ($nameZh) {
            $tagDataToInsert['name_zh'] = $nameZh;
        }

        // 設置預設值
        $tagDataToInsert['category'] = $tagData['category'] ?? null;
        $tagDataToInsert['emotion_type'] = $tagData['emotion_type'] ?? null;
        $tagDataToInsert['color'] = $tagData['color'] ?? null;

        $tag = Tag::create($tagDataToInsert);
        
        return $tag->id;
    }

    /**
     * 刪除用戶標籤（支援 custom tag 刪除 + default tag 隱藏）
     *
     * @param int $cardId
     * @param int $userId
     * @param array $tags 格式: [
     *   ['tag_id' => 1],  // 不提供 position 則刪除所有位置的該標籤
     *   ['tag_id' => 1, 'position' => 'upright'],  // 只刪除指定位置
     *   ['name_zh' => '標籤名稱'],  // 通過名稱刪除所有位置
     *   ...
     * ]
     * @return array 返回刪除後的 active 標籤
     */
    public function deleteCustomTags(int $cardId, int $userId, array $tags): array
    {
        $card = TarotCard::findOrFail($cardId);

        // 確保用戶存在
        $this->ensureUserExists($userId);

        DB::beginTransaction();
        try {
            $totalAffected = 0;
            $notFound = [];

            foreach ($tags as $tag) {
                // 確定 tag_id
                $tagId = null;

                if (isset($tag['tag_id'])) {
                    $tagExists = Tag::where('id', $tag['tag_id'])->exists();
                    if (!$tagExists) {
                        $notFound[] = '標籤 ID ' . $tag['tag_id'] . ' 不存在';
                        continue;
                    }
                    $tagId = $tag['tag_id'];
                } elseif (isset($tag['name_zh']) || isset($tag['name'])) {
                    $tagId = $this->findTag($tag);
                    if (!$tagId) {
                        $tagName = $tag['name_zh'] ?? $tag['name'];
                        $notFound[] = '找不到標籤「' . $tagName . '」';
                        continue;
                    }
                }

                if (!$tagId) {
                    continue;
                }

                $position = $tag['position'] ?? null;
                $validPosition = $position && in_array($position, ['upright', 'reversed', 'both']);

                // 1. 先嘗試刪除 custom tag
                $customQuery = DB::table('card_tags')
                    ->where('card_id', $cardId)
                    ->where('tag_id', $tagId)
                    ->where('user_id', $userId)
                    ->where('is_default', false);

                if ($validPosition) {
                    $customQuery->where('position', $position);
                }

                $deleted = $customQuery->delete();
                $totalAffected += $deleted;

                // 2. 如果不是 custom tag，檢查是否為 default tag，是的話隱藏它
                if ($deleted === 0) {
                    $defaultQuery = DB::table('card_tags')
                        ->where('card_id', $cardId)
                        ->where('tag_id', $tagId)
                        ->where('is_default', true)
                        ->whereNull('user_id');

                    if ($validPosition) {
                        $defaultQuery->where('position', $position);
                    }

                    $defaultEntries = $defaultQuery->get(['tag_id', 'position']);

                    if ($defaultEntries->isEmpty()) {
                        $identifier = $tag['name_zh'] ?? $tag['name'] ?? 'ID ' . $tagId;
                        $notFound[] = '標籤「' . $identifier . '」不在此牌的標籤中';
                        continue;
                    }

                    // 插入隱藏記錄
                    foreach ($defaultEntries as $entry) {
                        $alreadyHidden = DB::table('user_hidden_default_tags')
                            ->where('card_id', $cardId)
                            ->where('tag_id', $entry->tag_id)
                            ->where('position', $entry->position)
                            ->where('user_id', $userId)
                            ->exists();

                        if (!$alreadyHidden) {
                            DB::table('user_hidden_default_tags')->insert([
                                'card_id' => $cardId,
                                'tag_id' => $entry->tag_id,
                                'position' => $entry->position,
                                'user_id' => $userId,
                            ]);
                            $totalAffected++;
                        }
                    }
                }
            }

            if ($totalAffected === 0 && !empty($notFound)) {
                DB::rollBack();
                throw new \RuntimeException(implode('；', $notFound));
            }

            DB::commit();

            // 返回刪除後的 active 標籤
            return $this->getActiveTags($cardId, $userId);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * 取得使用者的有效標籤（default 排除隱藏 + custom）
     *
     * @param int $cardId
     * @param int $userId
     * @return array
     */
    public function getActiveTags(int $cardId, int $userId): array
    {
        $card = TarotCard::findOrFail($cardId);

        $this->ensureUserExists($userId);

        // 取得隱藏的 default tag 記錄
        $hiddenRecords = $card->getHiddenDefaultTagIds($userId);

        // 取得 default tags，排除被隱藏的
        $defaultTags = $card->defaultTags()->get()->filter(function ($tag) use ($hiddenRecords) {
            return !$hiddenRecords->contains(function ($hidden) use ($tag) {
                return $hidden->tag_id === $tag->id && $hidden->position === $tag->pivot->position;
            });
        })->map(function ($tag) {
            return [
                'id' => $tag->id,
                'name' => $tag->name,
                'name_zh' => $tag->name_zh,
                'position' => $tag->pivot->position,
                'is_default' => true,
            ];
        })->values();

        // 取得 custom tags
        $customTags = $card->customTags($userId)->get()->map(function ($tag) {
            return [
                'id' => $tag->id,
                'name' => $tag->name,
                'name_zh' => $tag->name_zh,
                'position' => $tag->pivot->position,
                'is_default' => false,
            ];
        });

        return $defaultTags->merge($customTags)->toArray();
    }

    /**
     * 重設回系統預設標籤（刪除所有用戶自訂標籤 + 清除隱藏記錄）
     *
     * @param int $cardId
     * @param int $userId
     * @return array 返回 active 標籤（即完整的 default 標籤）
     */
    public function resetToDefaultTags(int $cardId, int $userId): array
    {
        $card = TarotCard::findOrFail($cardId);

        // 確保用戶存在
        $this->ensureUserExists($userId);

        // 刪除該用戶的所有自訂標籤
        DB::table('card_tags')
            ->where('card_id', $cardId)
            ->where('user_id', $userId)
            ->where('is_default', false)
            ->delete();

        // 清除該用戶的所有隱藏記錄
        DB::table('user_hidden_default_tags')
            ->where('card_id', $cardId)
            ->where('user_id', $userId)
            ->delete();

        // 返回系統預設標籤
        return $this->getDefaultTags($cardId);
    }

}


