# Tarot Diary - Angular 版本

這是一個塔羅牌日記應用程式,使用 Angular 框架重構。

## 功能

- **首頁 (Home)**: 展示應用程式介紹和功能說明
- **新牌陣 (New Spread)**: 每日抽牌和記錄
- **歷史記錄 (History)**: 查看過往的塔羅牌記錄
- **數據分析 (Analysis)**: 分析標籤和關鍵字統計
- **設置 (Setting)**: 應用程式設置和配置

## 技術棧

- Angular 16
- TypeScript
- CSS3
- Responsive Design

## 專案結構

```
src/
├── app/
│   ├── components/          # 共享組件
│   │   ├── header/         # 導航頭部
│   │   └── footer/         # 頁腳
│   ├── pages/              # 頁面組件
│   │   ├── home/           # 首頁
│   │   ├── new-spread/     # 新牌陣
│   │   ├── history/        # 歷史記錄
│   │   ├── analysis/       # 數據分析
│   │   └── setting/        # 設置
│   ├── app-routing.module.ts  # 路由配置
│   └── app.module.ts          # 主模組
├── assets/
│   ├── images/             # 圖片資源
│   └── intlTelInput/       # 電話輸入庫
└── styles.css              # 全局樣式
```

## 安裝與運行

### 前置需求

- Node.js (v18 或更高版本)
- npm (v10 或更高版本)
- Angular CLI

### 安裝步驟

1. 進入專案目錄:
```bash
cd tarot-angular
```

2. 安裝依賴:
```bash
npm install
```

3. 啟動開發服務器:
```bash
ng serve
```

4. 在瀏覽器中打開:
```
http://localhost:4200
```

## 可用的命令

- `ng serve` - 啟動開發服務器
- `ng build` - 構建生產版本
- `ng test` - 運行單元測試
- `ng lint` - 運行代碼檢查

## 構建生產版本

```bash
ng build --configuration production
```

構建的文件將位於 `dist/` 目錄中。

## 路由

- `/` 或 `/home` - 首頁
- `/new-spread` - 新牌陣
- `/history` - 歷史記錄
- `/analysis` - 數據分析
- `/setting` - 設置

## 響應式設計

應用程式支持以下設備:

- 桌面 (1200px+)
- 平板 (768px - 1199px)
- 手機 (< 768px)

## 瀏覽器支持

- Chrome (最新版本)
- Firefox (最新版本)
- Safari (最新版本)
- Edge (最新版本)

## 開發說明

### 添加新組件

```bash
ng generate component components/your-component-name
```

### 添加新頁面

```bash
ng generate component pages/your-page-name
```

然後在 `app-routing.module.ts` 中添加路由配置。

## 貢獻

歡迎提交 Issue 和 Pull Request!

## 授權

此專案僅供學習和個人使用。


