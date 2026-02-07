# Tarot Diary - PostgreSQL 資料庫設定說明

## 資料庫配置

專案已配置為使用 PostgreSQL 資料庫，配置如下：

```env
DB_CONNECTION=pgsql
DB_HOST=postgres
DB_PORT=5432
DB_DATABASE=tarot_diary
DB_USERNAME=tarot_user
DB_PASSWORD=tarot_pass
REDIS_HOST=redis
```

## 資料庫架構

此專案包含完整的塔羅日記系統資料庫架構，共有 **19 個資料表**：

### 1. 用戶系統 (2 個表)
- `users` - 用戶基本資訊
- `user_preferences` - 用戶偏好設定

### 2. 塔羅牌基礎資料 (4 個表)
- `suits` - 花色表（權杖、聖杯、寶劍、錢幣）
- `tarot_cards` - 塔羅牌主表（78張牌）
- `tags` - 標籤表（情緒、事件、人物等）
- `card_tags` - 牌卡與標籤關聯表

### 3. 牌陣系統 (2 個表)
- `spread_types` - 牌陣類型（單張、三張、塞爾特十字等）
- `spread_positions` - 牌陣位置定義

### 4. 抽牌與記錄 (3 個表)
- `daily_draws` - 每日抽牌記錄
- `spread_readings` - 牌陣占卜記錄
- `spread_cards` - 牌陣中的每張牌

### 5. 回顧系統 (3 個表)
- `daily_reviews` - 每日回顧
- `review_tag_matches` - 標籤符合度記錄
- `spread_reviews` - 牌陣回顧

### 6. 統計分析 (3 個表)
- `card_statistics` - 牌卡統計
- `tag_statistics` - 標籤統計
- `user_monthly_stats` - 用戶月度統計

### 7. 提醒系統 (2 個表)
- `reminder_logs` - 提醒記錄
- `reminder_queue` - 提醒佇列

### 8. 輔助功能 (2 個表)
- `user_favorite_cards` - 用戶收藏的牌
- `user_journals` - 用戶日記/筆記

## PostgreSQL 特性

### 自動更新 updated_at 欄位
專案使用 PostgreSQL 觸發器自動更新 `updated_at` 欄位：
- `tarot_cards` 表
- `user_journals` 表
- `card_statistics` 表（使用 `last_updated` 欄位）
- `tag_statistics` 表（使用 `last_updated` 欄位）

### CHECK 約束
- `daily_reviews.overall_match_score` - 必須在 1-5 之間
- `spread_reviews.overall_accuracy` - 必須在 1-5 之間

### 索引優化
系統已創建以下索引以提升查詢效能：
- 用戶查詢索引
- 日期範圍查詢索引
- 外鍵關聯索引
- 提醒排程索引

## 執行 Migration

### 1. 確保 PostgreSQL 服務運行
```bash
# 如果使用 Docker
docker-compose up -d postgres
```

### 2. 執行資料庫遷移
```bash
php artisan migrate
```

### 3. 回滾 Migration（如需要）
```bash
# 回滾上一批次
php artisan migrate:rollback

# 回滾所有遷移
php artisan migrate:reset

# 重新執行所有遷移
php artisan migrate:fresh
```

### 4. 查看 Migration 狀態
```bash
php artisan migrate:status
```

## Migration 文件列表

所有 migration 文件都位於 `database/migrations/` 目錄中，按照正確的執行順序創建：

1. `create_users_table` - 用戶表
2. `create_user_preferences_table` - 用戶偏好
3. `create_suits_table` - 花色
4. `create_tags_table` - 標籤
5. `create_tarot_cards_table` - 塔羅牌
6. `create_card_tags_table` - 牌卡標籤關聯
7. `create_spread_types_table` - 牌陣類型
8. `create_spread_positions_table` - 牌陣位置
9. `create_daily_draws_table` - 每日抽牌
10. `create_spread_readings_table` - 牌陣記錄
11. `create_spread_cards_table` - 牌陣牌卡
12. `create_daily_reviews_table` - 每日回顧
13. `create_review_tag_matches_table` - 回顧標籤
14. `create_spread_reviews_table` - 牌陣回顧
15. `create_card_statistics_table` - 牌卡統計
16. `create_tag_statistics_table` - 標籤統計
17. `create_user_monthly_stats_table` - 月度統計
18. `create_reminder_logs_table` - 提醒日誌
19. `create_reminder_queue_table` - 提醒佇列
20. `create_user_favorite_cards_table` - 收藏牌卡
21. `create_user_journals_table` - 用戶日記
22. `create_updated_at_triggers` - PostgreSQL 觸發器

## 與 MySQL 的主要差異

本專案已將原始 MySQL 語法完全轉換為 PostgreSQL 相容格式：

1. **AUTO_INCREMENT → SERIAL/BIGSERIAL**
   - Laravel 的 `$table->id()` 自動處理

2. **ENUM → VARCHAR**
   - 使用 `VARCHAR` 配合應用層驗證
   - 更靈活且易於維護

3. **ON UPDATE CURRENT_TIMESTAMP → 觸發器**
   - 使用 PostgreSQL 函數和觸發器自動更新時間戳

4. **DELIMITER → $$ 語法**
   - PostgreSQL 使用 `$$` 作為函數定義分隔符

5. **時間戳欄位**
   - 使用 `timestamp` 替代 MySQL 的 `TIMESTAMP`
   - 使用 `useCurrent()` 設定預設值

## 後續步驟

1. **創建 Seeder** - 填充基礎資料（78張塔羅牌、花色、預設標籤）
2. **創建 Model** - 建立 Eloquent 模型與關聯
3. **設定外鍵約束** - 確保資料完整性
4. **效能優化** - 根據實際使用情況調整索引

## 注意事項

- 確保安裝 `php-pgsql` 擴展
- PostgreSQL 預設區分大小寫，注意表名和欄位名
- 建議定期備份資料庫
- 在正式環境中使用強密碼

## 技術堆疊

- **框架**: Laravel 11.x
- **資料庫**: PostgreSQL 14+
- **快取**: Redis
- **PHP**: 8.2+

---

創建日期: 2026-02-07
作者: AI Assistant

