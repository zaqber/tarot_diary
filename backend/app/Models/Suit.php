<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Suit extends Model
{
    /**
     * 取得屬於此花色的所有塔羅牌
     *
     * @return HasMany
     */
    public function tarotCards(): HasMany
    {
        return $this->hasMany(TarotCard::class);
    }
}
