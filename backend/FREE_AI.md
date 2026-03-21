# 其他 AI 後端（選用）

塔羅解牌**預設為 Google Gemini**，請先看 **`GEMINI_AI.md`**。

本頁僅列出 **可改用的其他後端**（配額／付費以官方為準）。

---

## Groq（免費額度）

1. [Groq Console](https://console.groq.com/) → [API Keys](https://console.groq.com/keys)  
2. `.env`：
   ```env
   AI_PROVIDER=groq
   GROQ_API_KEY=你的金鑰
   ```

---

## 付費：Grok（xAI）

見 **`GROK_AI.md`**（`AI_PROVIDER=grok`、`XAI_API_KEY`）。

---

## 付費：Claude（Anthropic）

見 **`CLAUDE_AI.md`**（`AI_PROVIDER=claude`、`ANTHROPIC_API_KEY`）。

---

## 對照

| AI_PROVIDER | 金鑰變數 |
|---------------|----------|
| `gemini`（預設） | `GEMINI_API_KEY` |
| `groq` | `GROQ_API_KEY` |
| `grok` | `XAI_API_KEY` |
| `claude` | `ANTHROPIC_API_KEY` |
