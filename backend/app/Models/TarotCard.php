<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class TarotCard extends Model
{
    /**
     * 取得所屬的花色
     *
     * @return BelongsTo
     */
    public function suit(): BelongsTo
    {
        return $this->belongsTo(Suit::class);
    }

    /**
     * 取得所有標籤（包含系統預設和用戶自訂）
     *
     * @return BelongsToMany
     */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'card_tags', 'card_id', 'tag_id')
                    ->withPivot('position', 'is_default', 'user_id');
    }

    /**
     * 取得系統預設標籤
     *
     * @return BelongsToMany
     */
    public function defaultTags(): BelongsToMany
    {
        return $this->tags()
                    ->wherePivot('is_default', true)
                    ->wherePivotNull('user_id');
    }

    /**
     * 取得用戶自訂標籤
     *
     * @param int|null $userId
     * @return BelongsToMany
     */
    public function customTags(?int $userId = null): BelongsToMany
    {
        $query = $this->tags()
                      ->wherePivot('is_default', false);
        
        if ($userId !== null) {
            $query->wherePivot('user_id', $userId);
        }
        
        return $query;
    }

    /**
     * 取得正位標籤
     *
     * @return BelongsToMany
     */
    public function uprightTags(): BelongsToMany
    {
        return $this->tags()->whereIn('card_tags.position', ['upright', 'both']);
    }

    /**
     * 取得逆位標籤
     *
     * @return BelongsToMany
     */
    public function reversedTags(): BelongsToMany
    {
        return $this->tags()->whereIn('card_tags.position', ['reversed', 'both']);
    }

    /**
     * 取得使用者隱藏的預設標籤 ID 與 position 列表
     *
     * @param int $userId
     * @return \Illuminate\Support\Collection
     */
    public function getHiddenDefaultTagIds(int $userId): \Illuminate\Support\Collection
    {
        return \Illuminate\Support\Facades\DB::table('user_hidden_default_tags')
            ->where('card_id', $this->id)
            ->where('user_id', $userId)
            ->get(['tag_id', 'position']);
    }
}
