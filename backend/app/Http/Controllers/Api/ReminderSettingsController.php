<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
class ReminderSettingsController extends Controller
{
    /**
     * GET /api/me/reminder-settings
     */
    public function show(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user) {
            return $this->errorResponse('未登入', 401);
        }

        return $this->successResponse($this->formatSettings($user), '取得提醒設定成功');
    }

    /**
     * PATCH /api/me/reminder-settings
     */
    public function update(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user) {
            return $this->errorResponse('未登入', 401);
        }

        $validated = $request->validate([
            'timezone' => ['sometimes', 'string', 'max:50', $this->timezoneRule()],
            'morning_reminder_time' => ['sometimes', 'string', 'regex:/^\d{2}:\d{2}(:\d{2})?$/'],
            'evening_reminder_time' => ['sometimes', 'string', 'regex:/^\d{2}:\d{2}(:\d{2})?$/'],
            'is_morning_reminder_enabled' => ['sometimes', 'boolean'],
            'is_evening_reminder_enabled' => ['sometimes', 'boolean'],
        ]);

        if (isset($validated['morning_reminder_time'])) {
            $validated['morning_reminder_time'] = $this->normalizeTime($validated['morning_reminder_time']);
        }
        if (isset($validated['evening_reminder_time'])) {
            $validated['evening_reminder_time'] = $this->normalizeTime($validated['evening_reminder_time']);
        }

        $user->fill($validated);
        $user->save();

        return $this->successResponse($this->formatSettings($user->fresh()), '已更新提醒設定');
    }

    /**
     * POST /api/me/telegram/link-token
     */
    public function createTelegramLinkToken(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user) {
            return $this->errorResponse('未登入', 401);
        }

        $token = config('services.telegram.bot_token');
        $username = ltrim((string) config('services.telegram.bot_username'), '@');
        if (!$token || $username === '') {
            return $this->errorResponse('伺服器尚未設定 TELEGRAM_BOT_TOKEN 或 TELEGRAM_BOT_USERNAME', 503);
        }

        $linkToken = bin2hex(random_bytes(16));
        $user->update([
            'telegram_link_token' => $linkToken,
            'telegram_link_token_expires_at' => now()->addHour(),
        ]);

        $deepLink = 'https://t.me/'.$username.'?start='.$linkToken;

        return $this->successResponse([
            'deep_link' => $deepLink,
            'expires_at' => $user->fresh()?->telegram_link_token_expires_at?->toIso8601String(),
        ], '已產生綁定連結（1 小時內有效）');
    }

    /**
     * POST /api/me/telegram/unlink
     */
    public function unlinkTelegram(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user) {
            return $this->errorResponse('未登入', 401);
        }

        $user->update([
            'telegram_chat_id' => null,
            'telegram_link_token' => null,
            'telegram_link_token_expires_at' => null,
        ]);

        return $this->successResponse($this->formatSettings($user->fresh()), '已解除 Telegram 綁定');
    }

    private function formatSettings(\App\Models\User $user): array
    {
        return [
            'timezone' => $user->timezone ?? 'Asia/Taipei',
            'morning_reminder_time' => $this->timeForApi($user->morning_reminder_time ?? '08:00:00'),
            'evening_reminder_time' => $this->timeForApi($user->evening_reminder_time ?? '20:00:00'),
            'is_morning_reminder_enabled' => (bool) $user->is_morning_reminder_enabled,
            'is_evening_reminder_enabled' => (bool) $user->is_evening_reminder_enabled,
            'telegram_linked' => filled($user->telegram_chat_id),
        ];
    }

    private function timeForApi(mixed $value): string
    {
        $s = is_string($value) ? $value : (string) $value;

        return strlen($s) >= 5 ? substr($s, 0, 5) : $s;
    }

    private function normalizeTime(string $value): string
    {
        $value = trim($value);
        if (strlen($value) === 5) {
            return $value.':00';
        }

        return $value;
    }

    private function timezoneRule(): \Closure
    {
        return function (string $attribute, mixed $value, \Closure $fail): void {
            if (!is_string($value) || $value === '') {
                return;
            }
            try {
                new \DateTimeZone($value);
            } catch (\Exception) {
                $fail('時區無效。');
            }
        };
    }
}
