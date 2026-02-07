# 塔羅日記系統 - Seeder 使用指南

## 📦 已創建的 Seeder

### 1. **SuitsSeeder** - 花色資料
填充 4 個塔羅牌花色：
- 權杖 (Wands) - 火元素
- 聖杯 (Cups) - 水元素
- 寶劍 (Swords) - 風元素
- 錢幣 (Pentacles) - 土元素

### 2. **TarotCardsSeeder** - 78 張塔羅牌
完整的塔羅牌組：
- **22 張大阿爾克那** (Major Arcana)
  - 包含完整的牌義、關鍵字（正逆位）
  - 從愚者 (0) 到世界 (21)
  
- **56 張小阿爾克那** (Minor Arcana)
  - 權杖牌組：14 張
  - 聖杯牌組：14 張
  - 寶劍牌組：14 張
  - 錢幣牌組：14 張
  - 每組包含：王牌、2-10、侍者、騎士、王后、國王

### 3. **TagsSeeder** - 預設標籤
超過 60 個標籤，分為：
- **情緒標籤** (emotion)
  - 正面：喜悅、快樂、愛、希望、自信等
  - 負面：悲傷、焦慮、恐懼、憤怒、挫折等
  - 中性：冷靜、沉思等
  - 複雜：苦樂參半等
  
- **事件標籤** (event)
  - 工作、學習、旅行、會議、慶祝、衝突、決定等
  
- **人物標籤** (person)
  - 家人、伴侶、朋友、同事、導師等
  
- **關係標籤** (relationship)
  - 浪漫、友誼、親情、合作、緊張、和解等
  
- **情境標籤** (situation)
  - 成長、療癒、過渡、穩定、機會、挑戰等
  
- **職業標籤** (career)
  - 成功、升遷、專案、團隊合作、領導、創意等

### 4. **SpreadTypesSeeder** - 牌陣範例
7 種經典牌陣：
1. **單張牌** (1 張) - 初級
2. **三張牌陣** (3 張) - 初級
3. **塞爾特十字** (10 張) - 高級
4. **關係牌陣** (7 張) - 中級
5. **職業道路** (5 張) - 中級
6. **未來一年** (12 張) - 高級
7. **決策牌陣** (7 張) - 中級

每個牌陣都包含：
- 完整的位置定義
- 中英文名稱
- 詳細說明
- 難度級別

## 🚀 執行 Seeder

### 執行所有 Seeder
```bash
php artisan db:seed
```

### 執行特定 Seeder
```bash
# 只填充花色
php artisan db:seed --class=SuitsSeeder

# 只填充塔羅牌
php artisan db:seed --class=TarotCardsSeeder

# 只填充標籤
php artisan db:seed --class=TagsSeeder

# 只填充牌陣
php artisan db:seed --class=SpreadTypesSeeder
```

### 重新填充（清空並重新填充）
```bash
# ⚠️ 警告：這會刪除所有資料並重新建立
php artisan migrate:fresh --seed
```

## 📊 驗證資料

### 檢查資料數量
```bash
# 使用 tinker 檢查
php artisan tinker

# 執行以下命令：
DB::table('suits')->count();          // 應該是 4
DB::table('tarot_cards')->count();    // 應該是 78
DB::table('tags')->count();           // 應該超過 60
DB::table('spread_types')->count();   // 應該是 7
DB::table('spread_positions')->count(); // 應該是所有牌陣位置總和
```

### 查看資料範例
```bash
# 在 tinker 中執行：
DB::table('tarot_cards')->where('card_type', 'major')->get();
DB::table('tags')->where('category', 'emotion')->get();
DB::table('spread_types')->first();
```

## 🔧 自訂 Seeder

### 新增更多標籤
編輯 `database/seeders/TagsSeeder.php`，在 `$tags` 陣列中新增：

```php
[
    'name' => 'your_tag',
    'name_zh' => '您的標籤',
    'category' => 'emotion',  // 或其他類別
    'emotion_type' => 'positive',
    'color' => '#FF0000',
],
```

### 新增牌陣
編輯 `database/seeders/SpreadTypesSeeder.php`：

1. 在 `$spreadTypes` 陣列中新增牌陣定義
2. 在 `createSpreadPositions()` 方法中新增對應的位置定義

### 修改塔羅牌內容
編輯 `database/seeders/TarotCardsSeeder.php`：
- 修改 `official_meaning_upright` 和 `official_meaning_reversed`
- 更新 `keywords_upright` 和 `keywords_reversed`

## 📝 注意事項

### 執行順序
Seeder 會按照依賴關係執行：
1. 獨立資料（花色、標籤）
2. 依賴資料（塔羅牌依賴花色）
3. 牌陣資料

### 資料完整性
- 所有塔羅牌都已關聯到正確的花色
- 大阿爾克那沒有花色（suit_id 為 null）
- 所有牌陣都有完整的位置定義

### 字元編碼
- 確保資料庫使用 UTF-8 編碼
- 所有中文內容都已正確編碼

## 🎯 後續步驟

填充資料後，您可以：

1. **建立 Model 關聯**
   ```bash
   php artisan make:model TarotCard
   php artisan make:model Tag
   php artisan make:model SpreadType
   ```

2. **測試查詢**
   ```php
   // 獲取所有大阿爾克那
   $majorArcana = DB::table('tarot_cards')
       ->where('card_type', 'major')
       ->get();

   // 獲取權杖牌組
   $wands = DB::table('tarot_cards')
       ->join('suits', 'tarot_cards.suit_id', '=', 'suits.id')
       ->where('suits.name_zh', '權杖')
       ->get();

   // 獲取情緒標籤
   $emotionTags = DB::table('tags')
       ->where('category', 'emotion')
       ->get();
   ```

3. **建立 API 端點**
   - 獲取所有塔羅牌
   - 按花色篩選
   - 按標籤搜尋
   - 獲取牌陣列表

## 🐛 疑難排解

### 錯誤：外鍵約束失敗
- 確保按正確順序執行 Seeder
- 先執行 `SuitsSeeder`，再執行 `TarotCardsSeeder`

### 錯誤：重複鍵值
- 如果已經執行過 Seeder，使用 `migrate:fresh --seed` 清空重建
- 或手動刪除資料後重新執行

### 資料未顯示中文
- 檢查資料庫字元集：`SHOW VARIABLES LIKE 'character_set%';`
- 確保使用 UTF-8 編碼

## 📚 相關文件

- `DATABASE_SETUP.md` - 資料庫架構說明
- `QUICKSTART.md` - 快速啟動指南
- `DATABASE_CONNECTION_GUIDE.md` - 資料庫連接指南

---

**資料來源說明**：
- 塔羅牌牌義參考經典 Rider-Waite 系統
- 標籤設計考慮東方文化和使用習慣
- 牌陣範例來自常見的塔羅占卜實務

🎴 祝您使用愉快！

