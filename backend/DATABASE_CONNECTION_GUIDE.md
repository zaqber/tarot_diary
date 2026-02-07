# 資料庫連接指南

## ⚠️ 重要提醒

Migration 已成功執行，**所有 29 個表格都已在資料庫中創建**！

如果您的資料庫客戶端顯示為空，請確認您連接到了**正確的資料庫**。

## 🔍 問題診斷

PostgreSQL 伺服器中有多個資料庫：

1. **`postgres`** - PostgreSQL 預設資料庫（空的）
2. **`tarot_diary`** ⭐ - 我們的應用資料庫（有 29 個表格）
3. `template0` - 系統範本
4. `template1` - 系統範本

**您需要連接到 `tarot_diary` 資料庫！**

## ✅ 正確的連接設定

請在您的資料庫客戶端（DBeaver、pgAdmin、TablePlus、DataGrip 等）中使用以下設定：

```
主機 (Host):         localhost
埠號 (Port):         5432
資料庫 (Database):   tarot_diary  ⚠️ 不是 postgres！
用戶名 (Username):   tarot_user
密碼 (Password):     tarot_pass
Schema:              public
```

## 📋 資料庫中的表格（29 個）

### 核心業務表格（21 個）

#### 用戶系統
- `users` - 用戶基本資訊
- `user_preferences` - 用戶偏好設定

#### 塔羅牌系統
- `suits` - 花色表
- `tarot_cards` - 塔羅牌主表
- `tags` - 標籤表
- `card_tags` - 牌卡標籤關聯

#### 牌陣系統
- `spread_types` - 牌陣類型
- `spread_positions` - 牌陣位置

#### 抽牌與記錄
- `daily_draws` - 每日抽牌
- `spread_readings` - 牌陣記錄
- `spread_cards` - 牌陣牌卡

#### 回顧系統
- `daily_reviews` - 每日回顧
- `review_tag_matches` - 標籤符合度
- `spread_reviews` - 牌陣回顧

#### 統計分析
- `card_statistics` - 牌卡統計
- `tag_statistics` - 標籤統計
- `user_monthly_stats` - 月度統計

#### 提醒系統
- `reminder_logs` - 提醒記錄
- `reminder_queue` - 提醒佇列

#### 輔助功能
- `user_favorite_cards` - 收藏牌卡
- `user_journals` - 用戶日記

### Laravel 系統表格（8 個）
- `migrations` - Migration 記錄
- `cache` - 快取表
- `cache_locks` - 快取鎖
- `sessions` - 會話表
- `jobs` - 佇列任務
- `job_batches` - 批次任務
- `failed_jobs` - 失敗任務
- `password_reset_tokens` - 密碼重設

## 🛠️ 常見資料庫客戶端設定

### 1. DBeaver

1. 新增連線 → PostgreSQL
2. 主要設定：
   - Host: `localhost`
   - Port: `5432`
   - Database: `tarot_diary` ⚠️
   - Username: `tarot_user`
   - Password: `tarot_pass`
3. 測試連線 → 完成

### 2. pgAdmin

1. 新增伺服器
2. 一般 → 名稱：`Tarot Diary`
3. 連線：
   - Host: `localhost`
   - Port: `5432`
   - Maintenance database: `tarot_diary` ⚠️
   - Username: `tarot_user`
   - Password: `tarot_pass`
4. 儲存

連接後展開：
```
Servers → Tarot Diary → Databases → tarot_diary → Schemas → public → Tables
```

### 3. TablePlus

1. 新增連線 → PostgreSQL
2. 設定：
   - Name: `Tarot Diary`
   - Host: `localhost`
   - Port: `5432`
   - User: `tarot_user`
   - Password: `tarot_pass`
   - Database: `tarot_diary` ⚠️
3. 連線

### 4. DataGrip (JetBrains)

1. 新增資料來源 → PostgreSQL
2. 設定：
   - Host: `localhost`
   - Port: `5432`
   - Database: `tarot_diary` ⚠️
   - User: `tarot_user`
   - Password: `tarot_pass`
3. 測試連線 → 套用

## 🔧 驗證連接（使用終端機）

如果仍有問題，可以使用終端機驗證：

```bash
# 方法 1：透過 Docker 容器連接
docker exec tarot-postgres psql -U tarot_user -d tarot_diary -c "\dt"

# 方法 2：本地 psql 連接
psql -h localhost -p 5432 -U tarot_user -d tarot_diary

# 然後執行：
\dt                    # 列出所有表格
\d users              # 查看 users 表結構
SELECT COUNT(*) FROM users;  # 查詢 users 表
```

## 📊 快速統計資訊

```bash
# 查看資料庫大小
docker exec tarot-postgres psql -U tarot_user -d tarot_diary -c "
  SELECT 
    pg_size_pretty(pg_database_size('tarot_diary')) AS size;
"

# 列出所有表格及其記錄數
docker exec tarot-postgres psql -U tarot_user -d tarot_diary -c "
  SELECT 
    schemaname,
    tablename,
    pg_size_pretty(pg_total_relation_size(schemaname||'.'||tablename)) AS size
  FROM pg_tables
  WHERE schemaname = 'public'
  ORDER BY pg_total_relation_size(schemaname||'.'||tablename) DESC;
"
```

## ❓ 疑難排解

### Q: 我看到 "Did not find any relations"
**A:** 您可能連接到了 `postgres` 資料庫而不是 `tarot_diary`。請確認資料庫名稱。

### Q: 連接被拒絕
**A:** 確認 Docker 容器正在運行：
```bash
docker compose ps
```

如果未運行，啟動它：
```bash
docker compose up -d
```

### Q: 密碼錯誤
**A:** 確認使用的是：
- Username: `tarot_user`
- Password: `tarot_pass`

### Q: 如何重新整理客戶端？
**A:** 大多數客戶端：
- 右鍵點擊資料庫 → 重新整理
- 或按 F5
- 或重新連線

## 📞 需要協助？

如果以上步驟都無法解決問題，請提供：
1. 您使用的資料庫客戶端名稱和版本
2. 連接時的完整錯誤訊息
3. 截圖（如果可能）

---

**確認重點**: 資料庫名稱必須是 `tarot_diary` 而不是 `postgres`！

