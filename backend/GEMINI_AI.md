# Google Gemini 塔羅解牌（預設）

## 設定

```env
AI_PROVIDER=gemini
GEMINI_API_KEY=你的金鑰
```

金鑰：https://aistudio.google.com/apikey  

`php artisan config:clear`

---

## 預設模型

預設為 **`gemini-2.5-flash-lite`**（省額度、適合長文解牌）。若要較強推理可改：

```env
GEMINI_MODEL=gemini-2.5-flash
```

**請避免**再使用 `gemini-2.0-flash`：官方已標示淘汰方向，且免費層常出現 **配額為 0 或極易 429**。

---

## HTTP 429／Quota exceeded／limit: 0

代表 **免費層請求或 token 已超過上限**，或該模型在你帳戶的免費額度為 0。

請依序嘗試：

1. **改模型**（最重要）：`.env` 設  
   `GEMINI_MODEL=gemini-2.5-flash-lite`  
   或 `gemini-2.5-flash`，然後 `php artisan config:clear`。
2. **查用量**：[ai.dev/rate-limit](https://ai.dev/rate-limit)、[Rate limits 說明](https://ai.google.dev/gemini-api/docs/rate-limits)。
3. **隔日再試**：免費層多為**每日**重置。
4. **啟用付費方案**：若 Google 要求綁定帳務才能使用特定模型，依 AI Studio／Cloud 畫面操作。

---

## 其他後端

若 Gemini 免費額度仍不敷使用，可改 `AI_PROVIDER`：見 **`FREE_AI.md`**。
