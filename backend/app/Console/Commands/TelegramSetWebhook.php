<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class TelegramSetWebhook extends Command
{
    protected $signature = 'telegram:set-webhook
                            {--url= : 覆寫基底網址（預設為 APP_URL），勿結尾斜線；須為 Telegram 可連的 HTTPS}';

    protected $description = '向 Telegram 註冊 Webhook：{APP_URL}/api/telegram/webhook/{TELEGRAM_WEBHOOK_SECRET}';

    public function handle(): int
    {
        $token = config('services.telegram.bot_token');
        $secret = config('services.telegram.webhook_secret');
        $base = rtrim((string) ($this->option('url') ?: config('app.url')), '/');

        if (!$token) {
            $this->error('請在 .env 設定 TELEGRAM_BOT_TOKEN');

            return self::FAILURE;
        }
        if ($secret === '' || $secret === null) {
            $this->error('請在 .env 設定 TELEGRAM_WEBHOOK_SECRET');

            return self::FAILURE;
        }
        if ($base === '' || str_starts_with($base, 'http://localhost') || str_starts_with($base, 'http://127.')) {
            $this->warn('APP_URL 為本機位址時，Telegram 無法連線。請用 ngrok 等取得 HTTPS 公開網址，例如：');
            $this->line('  php artisan telegram:set-webhook --url=https://xxxx.ngrok-free.app');

            return self::FAILURE;
        }
        if (! str_starts_with($base, 'https://')) {
            $this->error('Webhook 必須為 HTTPS（Telegram 要求）。目前：'.$base);

            return self::FAILURE;
        }

        $webhookUrl = $base.'/api/telegram/webhook/'.$secret;
        $api = sprintf('https://api.telegram.org/bot%s/setWebhook', $token);

        $response = Http::timeout(30)->asForm()->post($api, [
            'url' => $webhookUrl,
        ]);

        if (!$response->successful()) {
            $this->error('HTTP '.$response->status().': '.$response->body());

            return self::FAILURE;
        }

        $json = $response->json();
        if (!is_array($json) || empty($json['ok'])) {
            $this->error('Telegram 回應異常：'.$response->body());

            return self::FAILURE;
        }

        $this->info('Webhook 已設定：');
        $this->line($webhookUrl);

        return self::SUCCESS;
    }
}
