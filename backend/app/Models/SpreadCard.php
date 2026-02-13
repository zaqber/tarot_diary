<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SpreadCard extends Model
{
    /** 此表無 created_at / updated_at 欄位 */
    public $timestamps = false;

    protected $fillable = [
        'spread_reading_id',
        'position_number',
        'card_id',
        'is_reversed',
        'interpretation',
    ];

    protected $casts = [
        'is_reversed' => 'boolean',
    ];

    public function spreadReading(): BelongsTo
    {
        return $this->belongsTo(SpreadReading::class);
    }

    public function card(): BelongsTo
    {
        return $this->belongsTo(TarotCard::class, 'card_id');
    }

    public function tagSelections(): HasMany
    {
        return $this->hasMany(SpreadCardTagSelection::class);
    }
}
