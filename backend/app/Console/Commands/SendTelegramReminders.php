<?php

namespace App\Console\Commands;

use App\Models\ReminderLog;
use App\Models\User;
use App\Services\TelegramBotService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class SendTelegramReminders extends Command
{
    protected $signature = 'reminders:send-telegram';

    protected $description = '依使用者時區與設定時間，透過 Telegram 發送早晨／傍晚提醒';

    public function handle(TelegramBotService $telegram): int
    {
        if (!$telegram->isConfigured()) {
            $this->warn('略過：未設定 TELEGRAM_BOT_TOKEN');

            return self::SUCCESS;
        }

        $count = 0;
        User::query()
            ->where('is_active', true)
            ->whereNotNull('telegram_chat_id')
            ->chunkById(100, function ($users) use ($telegram, &$count): void {
                foreach ($users as $user) {
                    $count += $this->processUser($user, $telegram);
                }
            });

        if ($count > 0) {
            $this->info("已送出 {$count} 則提醒。");
        }

        return self::SUCCESS;
    }

    private function processUser(User $user, TelegramBotService $telegram): int
    {
        $tz = $user->timezone ?: 'Asia/Taipei';
        try {
            $nowLocal = now()->timezone($tz);
        } catch (\Exception) {
            $nowLocal = now()->timezone('Asia/Taipei');
        }

        $hm = $nowLocal->format('H:i');
        $sent = 0;

        if ($user->is_morning_reminder_enabled && $this->timeMatches($user->morning_reminder_time, $hm)) {
            $sent += $this->trySend($user, 'morning_draw', $nowLocal, $telegram, $this->morningMessage());
        }

        if ($user->is_evening_reminder_enabled && $this->timeMatches($user->evening_reminder_time, $hm)) {
            $sent += $this->trySend($user, 'evening_review', $nowLocal, $telegram, $this->eveningMessage());
        }

        return $sent;
    }

    private function timeMatches(mixed $dbTime, string $hm): bool
    {
        $s = is_string($dbTime) ? $dbTime : (string) $dbTime;
        $prefix = strlen($s) >= 5 ? substr($s, 0, 5) : $s;

        return $prefix === $hm;
    }

    private function trySend(
        User $user,
        string $type,
        \Carbon\CarbonInterface $nowLocal,
        TelegramBotService $telegram,
        string $text
    ): int {
        $dateKey = $nowLocal->toDateString();
        $cacheKey = sprintf('telegram_reminder:%d:%s:%s', $user->id, $type, $dateKey);
        if (Cache::has($cacheKey)) {
            return 0;
        }

        $chatId = $user->telegram_chat_id;
        if (!$chatId) {
            return 0;
        }

        $title = $type === 'morning_draw' ? '早晨抽牌提醒' : '傍晚回顧提醒';
        $scheduled = $nowLocal->copy()->startOfMinute();

        $log = ReminderLog::query()->create([
            'user_id' => $user->id,
            'reminder_type' => $type,
            'scheduled_time' => $scheduled,
            'sent_time' => null,
            'is_sent' => false,
            'is_clicked' => false,
            'click_time' => null,
            'title' => $title,
            'message' => $text,
            'created_at' => now(),
        ]);

        $result = $telegram->sendMessage($chatId, $text);
        if ($result['ok']) {
            $log->update([
                'is_sent' => true,
                'sent_time' => now(),
            ]);
            Cache::put($cacheKey, true, now()->addHours(36));

            return 1;
        }

        $log->update([
            'message' => ($text."\n[錯誤] ".($result['error'] ?? 'unknown')),
        ]);

        return 0;
    }

    private function morningMessage(): string
    {
        return "☀️ 塔羅日記：早晨提醒\n該為今天抽一組牌、寫下心情了。\n打開 App 開始「New Spread」吧。";
    }

    private function eveningMessage(): string
    {
        return "🌙 塔羅日記：傍晚提醒\n今天過得怎麼樣？回顧牌意與關鍵字，到 History 補上筆記吧。";
    }
}
