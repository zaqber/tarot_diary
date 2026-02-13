<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SpreadCardTagSelection extends Model
{
    public $timestamps = false;

    protected $fillable = ['spread_card_id', 'tag_id'];

    public function spreadCard(): BelongsTo
    {
        return $this->belongsTo(SpreadCard::class);
    }

    public function tag(): BelongsTo
    {
        return $this->belongsTo(Tag::class);
    }
}
