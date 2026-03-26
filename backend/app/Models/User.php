<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * 對應資料表欄位（與既有 migration 一致）
     *
     * @var list<string>
     */
    protected $fillable = [
        'username',
        'email',
        'password_hash',
        'display_name',
        'google_id',
        'timezone',
        'morning_reminder_time',
        'evening_reminder_time',
        'is_morning_reminder_enabled',
        'is_evening_reminder_enabled',
        'telegram_chat_id',
        'telegram_link_token',
        'telegram_link_token_expires_at',
        'last_login',
        'is_active',
    ];

    protected $hidden = [
        'password_hash',
        'remember_token',
        'telegram_link_token',
        'telegram_link_token_expires_at',
    ];

    protected function casts(): array
    {
        return [
            'last_login' => 'datetime',
            'is_active' => 'boolean',
            'is_morning_reminder_enabled' => 'boolean',
            'is_evening_reminder_enabled' => 'boolean',
            'telegram_link_token_expires_at' => 'datetime',
        ];
    }

    /**
     * Laravel 預設用 password 欄位驗證，我們使用 password_hash，在此對應
     */
    public function getAuthPassword(): string
    {
        return $this->password_hash ?? '';
    }

    /**
     * 設定密碼時自動雜湊並寫入 password_hash
     */
    public function setPasswordAttribute(?string $value): void
    {
        if ($value !== null && $value !== '') {
            $this->attributes['password_hash'] = bcrypt($value);
        }
    }
}
