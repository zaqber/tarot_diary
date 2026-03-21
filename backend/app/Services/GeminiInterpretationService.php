<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Google Gemini API（Google AI Studio 免費額度，有每日／分鐘上限）
 *
 * @see https://ai.google.dev/gemini-api/docs/quickstart
 */
class GeminiInterpretationService
{
    /**
     * @throws \RuntimeException
     */
    public function complete(string $userPrompt): string
    {
        $key = trim((string) config('services.gemini.api_key'));
        if ($key === '') {
            throw new \RuntimeException('伺服器未設定 GEMINI_API_KEY。請至 https://aistudio.google.com/apikey 建立免費金鑰。');
        }

        $model = trim((string) config('services.gemini.model', 'gemini-2.5-flash-lite'));
        if ($model === '') {
            $model = 'gemini-2.5-flash-lite';
        }

        $url = sprintf(
            'https://generativelanguage.googleapis.com/v1beta/models/%s:generateContent?key=%s',
            rawurlencode($model),
            rawurlencode($key)
        );

        $response = Http::timeout(120)
            ->acceptJson()
            ->asJson()
            ->post($url, [
                'contents' => [
                    [
                        'parts' => [['text' => $userPrompt]],
                    ],
                ],
                'generationConfig' => [
                    'maxOutputTokens' => 4096,
                ],
            ]);

        if (! $response->successful()) {
            $raw = $response->body();
            Log::warning('Gemini API error', [
                'status' => $response->status(),
                'model' => $model,
                'body' => $raw,
            ]);
            $json = $response->json();
            $detail = null;
            if (is_array($json)) {
                $detail = data_get($json, 'error.message')
                    ?? data_get($json, 'error.status');
            }
            $detail = $detail ? Str::limit((string) $detail, 400) : Str::limit($raw, 200);
            $hint = '';
            if ($response->status() === 429) {
                $hint = ' 建議：在 .env 設定 GEMINI_MODEL=gemini-2.5-flash-lite 或 gemini-2.5-flash（勿再用已限額的 gemini-2.0-flash）；並至 https://ai.dev/rate-limit 查看用量，或隔日再試。';
            }
            throw new \RuntimeException(
                'Gemini 請求失敗（HTTP '.$response->status().'）'.($detail !== '' ? '：'.$detail : '，請稍後再試。').$hint
            );
        }

        $json = $response->json();
        $candidates = $json['candidates'] ?? [];
        if ($candidates === []) {
            $block = data_get($json, 'promptFeedback.blockReason');
            throw new \RuntimeException(
                $block
                    ? 'Gemini 未產生內容（可能觸發安全篩選：'.$block.'）'
                    : 'Gemini 未產生內容，請稍後再試或換模型。'
            );
        }

        $parts = data_get($candidates, '0.content.parts', []);
        $text = '';
        foreach (is_array($parts) ? $parts : [] as $part) {
            if (isset($part['text'])) {
                $text .= (string) $part['text'];
            }
        }

        if (trim($text) === '') {
            throw new \RuntimeException('Gemini 回傳內容為空，請稍後再試。');
        }

        return $text;
    }
}
