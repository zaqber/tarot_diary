<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReminderLog extends Model
{
    protected $table = 'reminder_logs';

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'reminder_type',
        'scheduled_time',
        'sent_time',
        'is_sent',
        'is_clicked',
        'click_time',
        'title',
        'message',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_time' => 'datetime',
            'sent_time' => 'datetime',
            'click_time' => 'datetime',
            'is_sent' => 'boolean',
            'is_clicked' => 'boolean',
            'created_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
