<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * xAI Grok API（OpenAI 相容 chat/completions）
 *
 * @see https://docs.x.ai/docs/api-reference
 */
class GrokInterpretationService
{
    /**
     * @throws \RuntimeException
     */
    public function complete(string $userPrompt): string
    {
        $key = trim((string) config('services.xai.api_key'));
        if ($key === '') {
            throw new \RuntimeException('伺服器未設定 XAI_API_KEY，無法使用 Grok 解牌。');
        }

        $model = trim((string) config('services.xai.model', 'grok-4-fast-non-reasoning'));
        if ($model === '') {
            $model = 'grok-4-fast-non-reasoning';
        }

        $response = Http::timeout(120)
            ->withToken($key)
            ->acceptJson()
            ->post('https://api.x.ai/v1/chat/completions', [
                'model' => $model,
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => $userPrompt,
                    ],
                ],
                'max_tokens' => 4096,
            ]);

        if (! $response->successful()) {
            $raw = $response->body();
            Log::warning('xAI Grok API error', [
                'status' => $response->status(),
                'model' => $model,
                'body' => $raw,
            ]);
            $json = $response->json();
            $detail = null;
            if (is_array($json)) {
                $detail = $json['error']['message'] ?? $json['message'] ?? null;
            }
            $detail = $detail ? Str::limit((string) $detail, 400) : Str::limit($raw, 200);
            throw new \RuntimeException(
                'Grok 請求失敗（HTTP '.$response->status().'）'.($detail !== '' ? '：'.$detail : '，請稍後再試。')
            );
        }

        $json = $response->json();
        $text = (string) data_get($json, 'choices.0.message.content', '');

        if (trim($text) === '') {
            throw new \RuntimeException('Grok 回傳內容為空，請稍後再試。');
        }

        return $text;
    }
}
