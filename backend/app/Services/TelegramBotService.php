<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramBotService
{
    public function isConfigured(): bool
    {
        return (bool) config('services.telegram.bot_token');
    }

    /**
     * @return array{ok: bool, error?: string}
     */
    public function sendMessage(string $chatId, string $text): array
    {
        $token = config('services.telegram.bot_token');
        if (!$token) {
            return ['ok' => false, 'error' => 'TELEGRAM_BOT_TOKEN 未設定'];
        }

        $url = sprintf('https://api.telegram.org/bot%s/sendMessage', $token);
        $response = Http::timeout(15)->asJson()->post($url, [
            'chat_id' => $chatId,
            'text' => $text,
            'disable_web_page_preview' => true,
        ]);

        if (!$response->successful()) {
            $body = $response->json();
            $desc = is_array($body) ? ($body['description'] ?? $response->body()) : $response->body();
            Log::warning('Telegram sendMessage failed', ['status' => $response->status(), 'body' => $body]);

            return ['ok' => false, 'error' => is_string($desc) ? $desc : 'HTTP '.$response->status()];
        }

        $json = $response->json();
        if (!is_array($json) || empty($json['ok'])) {
            return ['ok' => false, 'error' => 'Telegram 回應異常'];
        }

        return ['ok' => true];
    }
}
