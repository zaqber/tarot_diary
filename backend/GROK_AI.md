# Grok（xAI）塔羅解牌（付費）

若要 **免費額度**，請改用 **`FREE_AI.md`**（Gemini / Groq）。

與其他後端由 **`AI_PROVIDER`** 決定。

## 設定 Grok

1. 至 [xAI Console](https://console.x.ai/) 註冊並儲值（API 為按量計費）。
2. [API Keys](https://console.x.ai/team/default/api-keys) 建立金鑰。
3. `.env`：

```env
AI_PROVIDER=grok
XAI_API_KEY=xai-你的金鑰
# 可選；預設 grok-4-fast-non-reasoning（價格較省，適合解牌長文）
# XAI_MODEL=grok-3-mini
```

4. `php artisan config:clear`

## 改回 Claude

```env
AI_PROVIDER=claude
ANTHROPIC_API_KEY=sk-ant-...
```

## 說明

- **Grok** = xAI（`api.x.ai`）。  
- **Groq**（拼字不同）= 另一家公司，本專案未內建，若要再接需另寫 Service。

模型與價格以 [xAI Models](https://docs.x.ai/docs/models) 為準。
