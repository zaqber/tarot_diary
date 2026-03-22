<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Groq Cloud API（免費額度、OpenAI 相容 chat/completions）
 *
 * @see https://console.groq.com/docs/quickstart
 */
class GroqInterpretationService
{
    /**
     * @throws \RuntimeException
     */
    public function complete(string $userPrompt): string
    {
        $key = trim((string) config('services.groq.api_key'));
        if ($key === '') {
            throw new \RuntimeException('伺服器未設定 GROQ_API_KEY。請至 https://console.groq.com/keys 建立免費金鑰。');
        }

        $model = trim((string) config('services.groq.model', 'llama-3.3-70b-versatile'));
        if ($model === '') {
            $model = 'llama-3.3-70b-versatile';
        }

        $response = Http::timeout(120)
            ->withToken($key)
            ->acceptJson()
            ->post('https://api.groq.com/openai/v1/chat/completions', [
                'model' => $model,
                'messages' => [
                    ['role' => 'user', 'content' => $userPrompt],
                ],
                'max_tokens' => 4096,
            ]);

        if (! $response->successful()) {
            $raw = $response->body();
            Log::warning('Groq API error', [
                'status' => $response->status(),
                'model' => $model,
                'body' => $raw,
            ]);
            $json = $response->json();
            $detail = is_array($json)
                ? ($json['error']['message'] ?? $json['message'] ?? null)
                : null;
            $detail = $detail ? Str::limit((string) $detail, 400) : Str::limit($raw, 200);
            throw new \RuntimeException(
                'Groq 請求失敗（HTTP '.$response->status().'）'.($detail !== '' ? '：'.$detail : '，請稍後再試。')
            );
        }

        $text = (string) data_get($response->json(), 'choices.0.message.content', '');
        if (trim($text) === '') {
            throw new \RuntimeException('Groq 回傳內容為空，請稍後再試。');
        }

        return $text;
    }
}
