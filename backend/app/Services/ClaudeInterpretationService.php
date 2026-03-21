<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ClaudeInterpretationService
{
    /**
     * @throws \RuntimeException
     */
    public function complete(string $userPrompt): string
    {
        $key = trim((string) config('services.anthropic.api_key'));
        if ($key === '') {
            throw new \RuntimeException('伺服器未設定 ANTHROPIC_API_KEY，無法使用 AI 解牌。');
        }

        $model = trim((string) config('services.anthropic.model', 'claude-3-5-sonnet-20241022'));
        if ($model === '') {
            $model = 'claude-3-5-sonnet-20241022';
        }

        $response = Http::timeout(120)
            ->withHeaders([
                'x-api-key' => $key,
                'anthropic-version' => '2023-06-01',
                'Content-Type' => 'application/json',
            ])
            ->post('https://api.anthropic.com/v1/messages', [
                'model' => $model,
                'max_tokens' => 4096,
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => $userPrompt,
                    ],
                ],
            ]);

        if (! $response->successful()) {
            $raw = $response->body();
            Log::warning('Anthropic API error', [
                'status' => $response->status(),
                'model' => $model,
                'body' => $raw,
            ]);
            $json = $response->json();
            $detail = is_array($json) ? ($json['error']['message'] ?? $json['message'] ?? null) : null;
            $detail = $detail ? Str::limit((string) $detail, 400) : Str::limit($raw, 200);
            throw new \RuntimeException(
                'AI 請求失敗（HTTP '.$response->status().'）'.($detail !== '' ? '：'.$detail : '，請稍後再試。')
            );
        }

        $json = $response->json();
        $blocks = $json['content'] ?? [];
        $text = '';
        foreach ($blocks as $block) {
            if (($block['type'] ?? '') === 'text') {
                $text .= (string) ($block['text'] ?? '');
            }
        }

        if (trim($text) === '') {
            throw new \RuntimeException('AI 回傳內容為空，請稍後再試。');
        }

        return $text;
    }
}
