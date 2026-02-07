# 關鍵字轉標籤系統說明

## 📌 概念說明

原本在 `tarot_cards` 表中，每張牌都有兩個欄位：
- `keywords_upright`: 正位關鍵字（逗號分隔的字串）
- `keywords_reversed`: 逆位關鍵字（逗號分隔的字串）

**新架構：** 這些關鍵字已經轉換為 `tags`，並透過 `card_tags` 建立多對多關聯。

## 🎯 優勢

### 1. 資料正規化
```
❌ 舊架構（字串）:
tarot_cards.keywords_upright = "新開始,希望,冒險,天真"

✅ 新架構（關聯）:
card_tags:
  - card_id=1, tag_id=15 (新開始), position=upright
  - card_id=1, tag_id=23 (希望), position=upright
  - card_id=1, tag_id=47 (冒險), position=upright
  - card_id=1, tag_id=89 (天真), position=upright
```

### 2. 支援正逆位獨立配置
```sql
-- 查詢「愚者」的正位標籤
SELECT t.name_zh 
FROM card_tags ct
JOIN tags t ON ct.tag_id = t.id
WHERE ct.card_id = 1 
  AND ct.position IN ('upright', 'both');

-- 查詢「愚者」的逆位標籤
SELECT t.name_zh 
FROM card_tags ct
JOIN tags t ON ct.tag_id = t.id
WHERE ct.card_id = 1 
  AND ct.position IN ('reversed', 'both');
```

### 3. 標籤可重用與統計
```sql
-- 查看「新開始」這個標籤被多少張牌使用
SELECT COUNT(DISTINCT card_id) as 使用次數
FROM card_tags
WHERE tag_id = (SELECT id FROM tags WHERE name_zh = '新開始');

-- 查看最常用的標籤 Top 10
SELECT t.name_zh, COUNT(*) as 使用次數
FROM card_tags ct
JOIN tags t ON ct.tag_id = t.id
GROUP BY t.id, t.name_zh
ORDER BY COUNT(*) DESC
LIMIT 10;
```

### 4. 用戶自訂標籤
```sql
-- 用戶可以為牌卡添加個人化標籤
INSERT INTO card_tags (card_id, tag_id, position, is_default, user_id)
VALUES (1, 123, 'upright', false, 456);

-- 查詢時可以同時顯示系統預設和用戶自訂
SELECT t.name_zh, ct.is_default
FROM card_tags ct
JOIN tags t ON ct.tag_id = t.id
WHERE ct.card_id = 1 
  AND (ct.user_id IS NULL OR ct.user_id = 456);
```

## 📊 轉換結果統計

```
✅ 轉換完成！
   新建標籤: 293 個
   新建關聯: 518 筆
   跳過重複: 28 筆

📊 目前統計:
   標籤總數: 393 個
   關聯總數: 546 筆
```

### 標籤分類分布
| 分類 | 標籤數量 |
|------|---------|
| general | 312 |
| emotion | 25 |
| event | 16 |
| situation | 13 |
| career | 10 |
| relationship | 9 |
| person | 8 |

## 🔍 查詢範例

### 範例 1：獲取牌卡的正逆位標籤
```php
// 在 Laravel 中使用 Eloquent
$card = TarotCard::with(['tags' => function($query) {
    $query->where('position', 'upright');
}])->find(1);

foreach ($card->tags as $tag) {
    echo $tag->name_zh . "\n";
}
```

```sql
-- 原始 SQL
SELECT tc.name_zh as 牌卡, ct.position as 位置, 
       STRING_AGG(t.name_zh, '、' ORDER BY t.name_zh) as 標籤
FROM tarot_cards tc
JOIN card_tags ct ON tc.id = ct.card_id
JOIN tags t ON ct.tag_id = t.id
WHERE tc.name_zh = '愚者'
GROUP BY tc.id, tc.name_zh, ct.position;
```

輸出：
```
 牌卡 |   位置   |           標籤           
------+----------+--------------------------
 愚者 | reversed | 不負責任、風險、魯莽
 愚者 | upright  | 冒險、天真、新開始、自由
```

### 範例 2：查找帶有特定標籤的所有牌卡
```sql
-- 查找所有帶有「愛」標籤的牌卡
SELECT tc.name_zh, ct.position
FROM card_tags ct
JOIN tarot_cards tc ON ct.card_id = tc.id
WHERE ct.tag_id = (SELECT id FROM tags WHERE name_zh = '愛')
ORDER BY tc.number;
```

### 範例 3：用戶每日抽牌時顯示標籤
```php
// 用戶抽到「戀人」正位
$draw = DailyDraw::create([
    'user_id' => $userId,
    'card_id' => $cardId,
    'is_reversed' => false,
    'draw_date' => today(),
]);

// 獲取該牌的正位標籤
$position = $draw->is_reversed ? 'reversed' : 'upright';
$tags = DB::table('card_tags')
    ->join('tags', 'card_tags.tag_id', '=', 'tags.id')
    ->where('card_tags.card_id', $cardId)
    ->whereIn('card_tags.position', [$position, 'both'])
    ->where(function($query) use ($userId) {
        $query->whereNull('card_tags.user_id')  // 系統預設
              ->orWhere('card_tags.user_id', $userId);  // 用戶自訂
    })
    ->select('tags.*', 'card_tags.is_default')
    ->get();

// 返回給前端
return [
    'card' => $card,
    'tags' => $tags,
];
```

### 範例 4：晚上回顧標記標籤符合度
```php
// 用戶回顧時標記哪些標籤符合今天的經歷
$review = DailyReview::create([
    'daily_draw_id' => $drawId,
    'overall_match_score' => 4,
    'review_note' => '今天確實有新的開始...',
]);

// 標記標籤符合度
foreach ($matchedTags as $tagId => $matched) {
    ReviewTagMatch::create([
        'review_id' => $review->id,
        'tag_id' => $tagId,
        'is_matched' => $matched,
        'match_strength' => 'strong', // weak/moderate/strong
        'specific_example' => '今天開始了新專案',
    ]);
}
```

## 🔄 轉換 Seeder 工作流程

`ConvertKeywordsToTagsSeeder` 的執行流程：

1. **讀取所有塔羅牌** 從 `tarot_cards` 表
2. **解析關鍵字** 將 `keywords_upright` 和 `keywords_reversed` 用逗號分割
3. **創建或查找標籤**
   - 先檢查中文名稱是否已存在
   - 如果不存在，生成英文名稱並創建新標籤
   - 自動分類（emotion/event/situation/career/general）
   - 自動判斷情緒類型（positive/negative/neutral）
   - 自動生成顯示顏色
4. **建立關聯** 在 `card_tags` 表中創建關聯記錄
5. **避免重複** 檢查是否已存在相同的關聯

## 📝 欄位保留說明

### keywords_upright 和 keywords_reversed 欄位

這兩個欄位**仍然保留**在 `tarot_cards` 表中，原因：
1. **向後兼容** 如果有舊代碼依賴這些欄位
2. **備份參考** 保留原始資料作為參考
3. **快速查看** 在資料庫管理工具中可以快速預覽

但在**實際應用中**，應該優先使用 `card_tags` 關聯來獲取標籤。

## 🎯 未來擴展

### 1. 標籤權重
可以在 `card_tags` 表中新增 `weight` 欄位，表示標籤對該牌的重要程度：
```sql
ALTER TABLE card_tags ADD COLUMN weight INTEGER DEFAULT 50;
```

### 2. 標籤群組
可以建立 `tag_groups` 表，將相關標籤分組：
```sql
CREATE TABLE tag_groups (
    id BIGSERIAL PRIMARY KEY,
    name VARCHAR(100),
    name_zh VARCHAR(100),
    description TEXT
);

CREATE TABLE tag_group_members (
    group_id BIGINT REFERENCES tag_groups(id),
    tag_id BIGINT REFERENCES tags(id),
    PRIMARY KEY (group_id, tag_id)
);
```

### 3. 用戶標籤學習
根據用戶的回顧記錄，自動調整標籤推薦：
```sql
-- 查看用戶最常匹配的標籤
SELECT t.name_zh, COUNT(*) as 匹配次數
FROM review_tag_matches rtm
JOIN tags t ON rtm.tag_id = t.id
JOIN daily_reviews dr ON rtm.review_id = dr.id
JOIN daily_draws dd ON dr.daily_draw_id = dd.id
WHERE dd.user_id = ? AND rtm.is_matched = true
GROUP BY t.id, t.name_zh
ORDER BY COUNT(*) DESC;
```

## 🛠️ 維護建議

### 定期清理
```sql
-- 查找沒有被使用的標籤
SELECT t.*
FROM tags t
LEFT JOIN card_tags ct ON t.id = ct.tag_id
WHERE ct.id IS NULL;
```

### 標籤合併
如果發現重複或相似的標籤，可以合併：
```sql
-- 將標籤 B 的所有關聯轉移到標籤 A
UPDATE card_tags 
SET tag_id = (SELECT id FROM tags WHERE name_zh = '標籤A')
WHERE tag_id = (SELECT id FROM tags WHERE name_zh = '標籤B');

-- 刪除標籤 B
DELETE FROM tags WHERE name_zh = '標籤B';
```

## 📚 相關文件

- `CARD_TAGS_MAPPING_GUIDE.md` - 牌卡標籤關聯機制
- `SEEDER_GUIDE.md` - Seeder 使用指南
- `DATABASE_SETUP.md` - 資料庫架構說明

---

**最後更新**: 2026-02-07

