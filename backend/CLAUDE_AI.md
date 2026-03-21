# Claude AI 塔羅解牌

- **預設 Gemini**：見 **`GEMINI_AI.md`**。  
- **其他後端**：見 **`FREE_AI.md`**、**`GROK_AI.md`**。

## 設定（AI_PROVIDER=claude）

1. 至 [Anthropic Console](https://console.anthropic.com/) 建立 API Key。
2. 在 `.env` 設定：

```env
ANTHROPIC_API_KEY=sk-ant-api03-...
# 選用；預設 claude-3-5-sonnet-20241022。勿寫 ANTHROPIC_MODEL= 留空，會導致 HTTP 400。
# ANTHROPIC_MODEL=claude-sonnet-4-6
```

3. 執行 migration（新增 `ai_question`、`ai_interpretation`、`ai_generated_at`）：

```bash
php artisan migrate
```

## API

- `POST /api/spread-readings/{id}/ai-interpret`（需登入）
- Body JSON（選填）：`{ "question": "我想問這週工作運勢…" }`
- 條件：該筆牌陣屬於目前使用者，且已抽滿三張牌。
- 成功後會寫入資料庫並回傳完整牌陣詳情（含 AI 欄位）。

重新呼叫會**覆寫**上一筆 AI 結果與當次提交的提問（若未填提問則 `ai_question` 為 null）。

## 常見錯誤

- **`Your credit balance is too low`**：Anthropic 帳戶 API 餘額不足。請至 Console 的 **Plans & Billing** 儲值或購買 credits，與應用程式程式碼無關。
