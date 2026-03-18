<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SpreadReading extends Model
{
    /** 此表無 created_at / updated_at 欄位 */
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'spread_type_id',
        'theme',
        'reading_date',
        'reading_time',
        'question',
        'overall_note',
        'is_reviewed',
    ];

    protected $casts = [
        'reading_date' => 'date',
        'reading_time' => 'datetime',
        'is_reviewed' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function spreadType(): BelongsTo
    {
        return $this->belongsTo(SpreadType::class);
    }

    public function spreadCards(): HasMany
    {
        return $this->hasMany(SpreadCard::class)->orderBy('position_number');
    }
}
