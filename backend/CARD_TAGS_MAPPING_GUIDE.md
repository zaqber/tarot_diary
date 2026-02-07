# Tarot Cards 與 Tags 的 Mapping 機制

## 📋 概述

`tarot_cards` 和 `tags` 透過 `card_tags` 中間表建立**多對多關聯**（Many-to-Many Relationship）。

## 🔗 資料表關係

```
┌─────────────────┐         ┌──────────────┐         ┌─────────────┐
│  tarot_cards    │         │  card_tags   │         │    tags     │
├─────────────────┤         ├──────────────┤         ├─────────────┤
│ id (PK)         │◄───────┤ card_id (FK) │         │ id (PK)     │
│ name            │         │ tag_id (FK)  ├────────►│ name        │
│ name_zh         │         │ position     │         │ name_zh     │
│ card_type       │         │ is_default   │         │ category    │
│ ...             │         │ user_id (FK) │         │ ...         │
└─────────────────┘         └──────────────┘         └─────────────┘
                                    │
                                    │
                                    ▼
                            ┌──────────────┐
                            │    users     │
                            ├──────────────┤
                            │ id (PK)      │
                            │ username     │
                            │ ...          │
                            └──────────────┘
```

## 🎯 `card_tags` 表結構

### 欄位說明

| 欄位 | 類型 | 說明 | 範例 |
|------|------|------|------|
| `id` | bigint | 主鍵 | 1, 2, 3... |
| `card_id` | bigint | 塔羅牌 ID | 1 (愚者) |
| `tag_id` | bigint | 標籤 ID | 5 (新開始) |
| `position` | varchar(20) | 正位/逆位/雙向 | 'upright', 'reversed', 'both' |
| `is_default` | boolean | 是否為系統預設 | true/false |
| `user_id` | bigint (nullable) | 用戶 ID (NULL=系統) | NULL, 123, 456... |

### 唯一約束

```sql
UNIQUE (card_id, tag_id, position, user_id)
```

這確保：
- 同一張牌的同一個標籤在同一位置（正/逆/雙向）對同一用戶只能有一筆記錄
- 但可以有多筆不同 position 的記錄

## 💡 設計特點

### 1. 支援正逆位不同標籤

塔羅牌正位和逆位的意義通常不同，因此需要分別配置標籤。

#### Position 欄位的三種值：

```
'upright'  → 僅適用於正位
'reversed' → 僅適用於逆位
'both'     → 正逆位都適用
```

#### 實際範例：

**愚者 (The Fool)**

| Position | 標籤 | 說明 |
|----------|------|------|
| upright | 新開始、希望、冒險 | 正位代表新的開始和機會 |
| reversed | 焦慮、魯莽、挫折 | 逆位代表不成熟或風險 |
| both | 選擇、改變 | 正逆位都涉及選擇和改變 |

**SQL 範例：**

```sql
-- 愚者正位：新開始
INSERT INTO card_tags (card_id, tag_id, position, is_default, user_id)
VALUES (1, 10, 'upright', true, NULL);

-- 愚者逆位：焦慮
INSERT INTO card_tags (card_id, tag_id, position, is_default, user_id)
VALUES (1, 15, 'reversed', true, NULL);

-- 愚者正逆位都有：改變
INSERT INTO card_tags (card_id, tag_id, position, is_default, user_id)
VALUES (1, 20, 'both', true, NULL);
```

### 2. 系統預設 vs 用戶自訂

#### 兩種標籤來源：

| is_default | user_id | 說明 | 誰能看到 |
|------------|---------|------|----------|
| `true` | `NULL` | 系統預設標籤 | 所有用戶 |
| `false` | `123` | 用戶 123 的自訂標籤 | 僅用戶 123 |

#### 使用場景：

**系統預設標籤**
```sql
-- 所有用戶都能看到「愚者」有「新開始」這個標籤
card_id=1, tag_id=10, is_default=true, user_id=NULL
```

**用戶自訂標籤**
```sql
-- 用戶 123 認為「愚者」也代表「迷茫」（個人解讀）
card_id=1, tag_id=25, is_default=false, user_id=123
```

### 3. 查詢範例

#### 查詢某張牌的所有標籤

```sql
-- 查詢「愚者」的所有正位標籤（包含系統和用戶自訂）
SELECT t.name_zh, ct.position, ct.is_default
FROM card_tags ct
JOIN tags t ON ct.tag_id = t.id
WHERE ct.card_id = 1  -- 愚者的 ID
  AND ct.position IN ('upright', 'both')
  AND (ct.user_id IS NULL OR ct.user_id = 123);  -- 系統標籤或用戶 123 的標籤
```

#### 查詢有特定標籤的所有牌

```sql
-- 查詢所有帶有「愛」標籤的牌
SELECT tc.name_zh, ct.position
FROM card_tags ct
JOIN tarot_cards tc ON ct.card_id = tc.id
WHERE ct.tag_id = (SELECT id FROM tags WHERE name_zh = '愛')
  AND ct.user_id IS NULL;  -- 只查系統預設
```

#### 查詢用戶的個人化標籤

```sql
-- 查詢用戶 123 為「愚者」自訂的標籤
SELECT t.name_zh, ct.position
FROM card_tags ct
JOIN tags t ON ct.tag_id = t.id
WHERE ct.card_id = 1
  AND ct.user_id = 123
  AND ct.is_default = false;
```

## 🎴 實際應用場景

### 場景 1: 每日抽牌

用戶抽到「愚者」正位：

```php
// 1. 獲取牌卡資訊
$card = DB::table('tarot_cards')->where('id', 1)->first();

// 2. 獲取該牌的正位標籤（系統 + 用戶自訂）
$tags = DB::table('card_tags')
    ->join('tags', 'card_tags.tag_id', '=', 'tags.id')
    ->where('card_tags.card_id', 1)
    ->whereIn('card_tags.position', ['upright', 'both'])
    ->where(function($query) use ($userId) {
        $query->whereNull('card_tags.user_id')
              ->orWhere('card_tags.user_id', $userId);
    })
    ->select('tags.*', 'card_tags.position')
    ->get();

// 結果：新開始、希望、冒險、改變...
```

### 場景 2: 晚上回顧

用戶回顧今天的「愚者」是否符合：

```php
// 用戶選擇今天確實經歷了「新開始」和「希望」
// 但沒有「冒險」的感覺

// 記錄到 review_tag_matches
DB::table('review_tag_matches')->insert([
    ['review_id' => 1, 'tag_id' => 10, 'is_matched' => true],  // 新開始 ✓
    ['review_id' => 1, 'tag_id' => 15, 'is_matched' => true],  // 希望 ✓
    ['review_id' => 1, 'tag_id' => 20, 'is_matched' => false], // 冒險 ✗
]);
```

### 場景 3: 用戶自訂標籤

用戶覺得「愚者」對他來說也代表「創業」：

```php
// 1. 確保標籤存在（或創建新標籤）
$tagId = DB::table('tags')
    ->where('name_zh', '創業')
    ->value('id');

// 2. 為用戶建立自訂關聯
DB::table('card_tags')->insert([
    'card_id' => 1,          // 愚者
    'tag_id' => $tagId,      // 創業
    'position' => 'upright', // 正位
    'is_default' => false,   // 用戶自訂
    'user_id' => 123,        // 用戶 ID
]);
```

## 📊 資料範例

### 範例 1: 愚者的標籤配置

```sql
card_tags 表：
┌────┬─────────┬────────┬──────────┬────────────┬─────────┐
│ id │ card_id │ tag_id │ position │ is_default │ user_id │
├────┼─────────┼────────┼──────────┼────────────┼─────────┤
│ 1  │ 1       │ 10     │ upright  │ true       │ NULL    │ → 系統：愚者正位=新開始
│ 2  │ 1       │ 15     │ upright  │ true       │ NULL    │ → 系統：愚者正位=希望
│ 3  │ 1       │ 20     │ reversed │ true       │ NULL    │ → 系統：愚者逆位=焦慮
│ 4  │ 1       │ 25     │ both     │ true       │ NULL    │ → 系統：愚者正逆=改變
│ 5  │ 1       │ 30     │ upright  │ false      │ 123     │ → 用戶123：愚者正位=創業
└────┴─────────┴────────┴──────────┴────────────┴─────────┘
```

### 範例 2: 多張牌共用標籤

「愛」這個標籤可以關聯到多張牌：

```sql
┌────┬─────────┬────────┬──────────┬────────────┬─────────┐
│ id │ card_id │ tag_id │ position │ is_default │ user_id │
├────┼─────────┼────────┼──────────┼────────────┼─────────┤
│ 10 │ 6       │ 5      │ upright  │ true       │ NULL    │ → 戀人正位=愛
│ 11 │ 42      │ 5      │ upright  │ true       │ NULL    │ → 聖杯王牌=愛
│ 12 │ 51      │ 5      │ upright  │ true       │ NULL    │ → 聖杯十=愛
└────┴─────────┴────────┴──────────┴────────────┴─────────┘
```

## 🔧 執行範例 Seeder

```bash
# 執行範例 Seeder（為部分牌卡建立標籤關聯）
php artisan db:seed --class=CardTagsSeeder

# 查看結果
php artisan tinker
>>> DB::table('card_tags')->count();
>>> DB::table('card_tags')->join('tarot_cards', 'card_tags.card_id', '=', 'tarot_cards.id')->join('tags', 'card_tags.tag_id', '=', 'tags.id')->select('tarot_cards.name_zh as card', 'tags.name_zh as tag', 'card_tags.position')->get();
```

## 🎯 建議的完整配置流程

### 階段 1: 系統預設標籤（管理員配置）

為所有 78 張牌配置基本的系統標籤：

1. 大阿爾克那（22 張）- 每張配置 3-5 個正位標籤、3-5 個逆位標籤
2. 小阿爾克那（56 張）- 每張配置 2-4 個標籤

### 階段 2: 用戶個人化（使用過程中）

用戶在使用過程中可以：

1. 為牌卡添加個人化標籤
2. 基於自己的經驗調整標籤
3. 建立專屬的牌義解讀系統

### 階段 3: 機器學習優化（未來）

基於用戶回顧資料：

1. 分析哪些標籤最常被標記為「符合」
2. 自動推薦標籤
3. 優化系統預設標籤

## 📝 注意事項

1. **唯一性約束**：同一張牌、同一個標籤、同一位置、同一用戶只能有一筆記錄
2. **NULL 處理**：`user_id = NULL` 代表系統預設，查詢時要特別處理
3. **Position 值**：只能是 'upright'、'reversed' 或 'both'
4. **級聯刪除**：刪除牌卡或標籤時，相關的 card_tags 記錄會自動刪除

## 🔍 相關查詢工具

### Laravel Eloquent 關聯（建議建立）

```php
// TarotCard Model
class TarotCard extends Model
{
    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'card_tags')
                    ->withPivot('position', 'is_default', 'user_id');
    }
    
    public function uprightTags()
    {
        return $this->tags()->whereIn('position', ['upright', 'both']);
    }
    
    public function reversedTags()
    {
        return $this->tags()->whereIn('position', ['reversed', 'both']);
    }
}

// Tag Model
class Tag extends Model
{
    public function cards()
    {
        return $this->belongsToMany(TarotCard::class, 'card_tags')
                    ->withPivot('position', 'is_default', 'user_id');
    }
}
```

---

**總結**：`card_tags` 是一個靈活的多對多關聯表，支援正逆位區分、系統與用戶自訂標籤，為塔羅日記系統提供了強大的標籤管理功能。

