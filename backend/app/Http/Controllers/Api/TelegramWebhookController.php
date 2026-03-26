<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\TelegramBotService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class TelegramWebhookController extends Controller
{
    public function __construct(
        private TelegramBotService $telegram
    ) {}

    /**
     * POST /api/telegram/webhook/{secret}
     * 至 @BotFather 設定 Webhook URL（須 HTTPS，本機可用 ngrok）
     */
    public function handle(Request $request, string $secret): Response|JsonResponse
    {
        $expected = (string) config('services.telegram.webhook_secret');
        if ($expected === '' || !hash_equals($expected, $secret)) {
            return response()->json(['ok' => false], 403);
        }

        $update = $request->all();
        $message = $update['message'] ?? null;
        if (!is_array($message)) {
            return response()->noContent();
        }

        $text = isset($message['text']) ? trim((string) $message['text']) : '';
        if ($text === '' || !str_starts_with($text, '/start')) {
            return response()->noContent();
        }

        $parts = preg_split('/\s+/', $text, 2);
        $token = isset($parts[1]) ? trim($parts[1]) : '';
        if ($token === '' || strlen($token) > 64) {
            return response()->noContent();
        }

        $chat = $message['chat'] ?? null;
        $chatId = is_array($chat) && isset($chat['id']) ? (string) $chat['id'] : null;
        if ($chatId === null) {
            return response()->noContent();
        }

        $user = User::query()
            ->where('telegram_link_token', $token)
            ->where('telegram_link_token_expires_at', '>', now())
            ->first();

        if (!$user) {
            $this->telegram->sendMessage(
                $chatId,
                '連結已過期或無效。請回到塔羅日記「提醒設定」重新產生綁定連結。'
            );

            return response()->noContent();
        }

        $user->update([
            'telegram_chat_id' => $chatId,
            'telegram_link_token' => null,
            'telegram_link_token_expires_at' => null,
        ]);

        $this->telegram->sendMessage(
            $chatId,
            '已成功綁定「塔羅日記」提醒。你會在設定的時間收到 Telegram 通知（請勿封鎖此 Bot）。'
        );

        return response()->noContent();
    }
}
