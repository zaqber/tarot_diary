<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Tag extends Model
{
    /**
     * 不使用時間戳記（tags 表沒有 created_at 和 updated_at）
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * 可批量賦值的欄位
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'name_zh',
        'category',
        'emotion_type',
        'color',
    ];

    /**
     * 取得所有關聯的塔羅牌
     *
     * @return BelongsToMany
     */
    public function cards(): BelongsToMany
    {
        return $this->belongsToMany(TarotCard::class, 'card_tags', 'tag_id', 'card_id')
                    ->withPivot('position', 'is_default', 'user_id');
    }
}
