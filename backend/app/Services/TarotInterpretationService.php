<?php

namespace App\Services;

/**
 * 塔羅 AI 解牌：預設 Google Gemini（AI_PROVIDER 可改 groq、grok、claude）
 */
class TarotInterpretationService
{
    public function __construct(
        protected ClaudeInterpretationService $claude,
        protected GrokInterpretationService $grok,
        protected GeminiInterpretationService $gemini,
        protected GroqInterpretationService $groq
    ) {}

    /**
     * @throws \RuntimeException
     */
    public function interpret(string $prompt): string
    {
        $provider = strtolower(trim((string) config('services.ai.provider', 'gemini')));

        return match (true) {
            in_array($provider, ['gemini', 'google'], true) => $this->gemini->complete($prompt),
            in_array($provider, ['groq'], true) => $this->groq->complete($prompt),
            in_array($provider, ['grok', 'xai'], true) => $this->grok->complete($prompt),
            default => $this->claude->complete($prompt),
        };
    }
}
