<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class TelegramWebhookInfo extends Command
{
    protected $signature = 'telegram:webhook-info';

    protected $description = '查詢目前 Bot 在 Telegram 註冊的 Webhook 網址與狀態';

    public function handle(): int
    {
        $token = config('services.telegram.bot_token');
        if (!$token) {
            $this->error('請在 .env 設定 TELEGRAM_BOT_TOKEN');

            return self::FAILURE;
        }

        $api = sprintf('https://api.telegram.org/bot%s/getWebhookInfo', $token);
        $response = Http::timeout(30)->get($api);

        if (!$response->successful()) {
            $this->error('HTTP '.$response->status().': '.$response->body());

            return self::FAILURE;
        }

        $json = $response->json();
        if (!is_array($json) || empty($json['ok']) || !isset($json['result']) || !is_array($json['result'])) {
            $this->error('回應異常：'.$response->body());

            return self::FAILURE;
        }

        $r = $json['result'];
        $this->table(
            ['項目', '值'],
            [
                ['url', (string) ($r['url'] ?? '')],
                ['pending_update_count', (string) ($r['pending_update_count'] ?? '')],
                ['last_error_message', (string) ($r['last_error_message'] ?? '')],
                ['last_error_date', isset($r['last_error_date']) ? (string) $r['last_error_date'] : ''],
            ]
        );

        return self::SUCCESS;
    }
}
