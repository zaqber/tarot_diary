# 塔羅日記系統 - 快速啟動指南

## 🚀 快速開始

### 1. 啟動資料庫服務

```bash
# 啟動 PostgreSQL 和 Redis
docker-compose up -d

# 查看服務狀態
docker-compose ps
```

### 2. 安裝 PHP 依賴

```bash
composer install
```

### 3. 執行資料庫遷移

```bash
# 執行所有 migration
php artisan migrate

# 查看 migration 狀態
php artisan migrate:status
```

### 4. 啟動開發伺服器

```bash
php artisan serve
```

伺服器將在 `http://localhost:8000` 啟動

## 📊 資料庫連線資訊

已配置的資料庫設定：

```
資料庫類型: PostgreSQL
主機: postgres (Docker) 或 localhost (本地)
埠號: 5432
資料庫名稱: tarot_diary
用戶名: tarot_user
密碼: tarot_pass
```

Redis 配置：
```
主機: redis (Docker) 或 localhost (本地)
埠號: 6379
```

## 🗂️ 資料庫架構總覽

系統包含 **19 個資料表**，涵蓋：

- ✨ **用戶系統**: 用戶管理與偏好設定
- 🎴 **塔羅牌系統**: 78張塔羅牌完整資料與標籤
- 📐 **牌陣系統**: 多種牌陣佈局支援
- 📅 **每日抽牌**: 早晚抽牌記錄
- 🔍 **回顧系統**: 標籤符合度追蹤
- 📈 **統計分析**: 個人化準確度統計
- ⏰ **提醒系統**: 早晚自動提醒
- 📝 **日記功能**: 心情與筆記記錄

詳細架構請參考 `DATABASE_SETUP.md`

## 🛠️ 常用指令

### 資料庫管理

```bash
# 重新執行所有 migration（會清空資料！）
php artisan migrate:fresh

# 回滾上一批次 migration
php artisan migrate:rollback

# 查看資料庫連線
php artisan db:show
```

### Docker 管理

```bash
# 停止所有服務
docker-compose down

# 停止並刪除資料（謹慎使用！）
docker-compose down -v

# 查看日誌
docker-compose logs postgres
docker-compose logs redis

# 進入 PostgreSQL 容器
docker exec -it tarot-postgres psql -U tarot_user -d tarot_diary
```

### 清除快取

```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

## 📝 下一步

1. **建立 Seeder** - 填充 78 張塔羅牌基礎資料
   ```bash
   php artisan make:seeder TarotCardsSeeder
   ```

2. **建立 Model** - 創建 Eloquent 模型
   ```bash
   php artisan make:model TarotCard
   php artisan make:model DailyDraw
   # ... 其他模型
   ```

3. **建立 Controller** - 開發 API 端點
   ```bash
   php artisan make:controller Api/TarotCardController --api
   ```

4. **設定 API 路由** - 在 `routes/api.php` 中定義路由

## 🔧 疑難排解

### 連線錯誤

如果遇到資料庫連線錯誤：

1. 確認 Docker 服務正在運行
   ```bash
   docker-compose ps
   ```

2. 檢查 `.env` 文件配置是否正確

3. 如果使用本地連線（非 Docker），將 `DB_HOST` 改為 `localhost`

### Migration 錯誤

如果 migration 失敗：

1. 檢查 PostgreSQL 版本（需要 14+）
2. 確認用戶有建表權限
3. 查看詳細錯誤訊息：
   ```bash
   php artisan migrate --verbose
   ```

## 📚 相關文件

- `DATABASE_SETUP.md` - 完整的資料庫架構說明
- `docker-compose.yml` - Docker 服務配置
- `database/migrations/` - 所有 migration 文件

## 💡 提示

- 開發時使用 `php artisan migrate:fresh` 可以快速重建資料庫
- 正式環境請務必定期備份資料庫
- 建議使用 `.env.example` 作為範本創建環境配置

---

祝您開發順利！🎴✨

