<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
}
