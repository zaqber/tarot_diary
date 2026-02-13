# 快速啟動指南

## 🚀 快速開始

### 1️⃣ 進入專案目錄
```bash
cd /home/doremi/Desktop/Tarot/tarot-angular
```

### 2️⃣ 安裝依賴 (如果還沒安裝)
```bash
npm install
```

### 3️⃣ 啟動開發服務器
```bash
ng serve
```

或者使用 `--open` 自動打開瀏覽器:
```bash
ng serve --open
```

### 4️⃣ 在瀏覽器中訪問
```
http://localhost:4200
```

## 📱 預覽不同設備

開發服務器支持在不同設備上預覽:

```bash
# 指定端口
ng serve --port 4300

# 允許外部訪問 (可在手機上測試)
ng serve --host 0.0.0.0
```

## 🔨 常用命令

### 開發模式
```bash
ng serve              # 啟動開發服務器
ng serve --open       # 啟動並自動打開瀏覽器
ng serve --port 4300  # 指定端口
```

### 構建專案
```bash
ng build                           # 開發版本構建
ng build --configuration production # 生產版本構建 (優化)
```

### 創建新組件
```bash
ng generate component components/your-component
ng generate component pages/your-page
```

### 創建新服務
```bash
ng generate service services/your-service
```

### 代碼檢查
```bash
ng lint
```

### 運行測試
```bash
ng test        # 單元測試
ng e2e         # 端到端測試
```

## 📁 重要文件說明

### `src/app/app.component.html`
主應用組件模板,包含 header、router-outlet、footer

### `src/app/app-routing.module.ts`
路由配置文件,定義所有頁面路由

### `src/app/app.module.ts`
主模組,聲明所有組件並導入必要的模組

### `src/styles.css`
全局樣式文件

### `src/assets/`
靜態資源目錄 (圖片、字體等)

## 🎨 開發技巧

### 1. 熱重載
保存文件後,瀏覽器會自動刷新,無需手動刷新。

### 2. 調試
在瀏覽器開發者工具中可以直接調試 TypeScript 代碼。

### 3. 樣式隔離
每個組件的樣式默認是隔離的,不會影響其他組件。

### 4. 路由導航
使用 `routerLink` 而不是 `href` 來導航:
```html
<a routerLink="/home">Home</a>
```

## 🐛 常見問題排除

### 端口被佔用
```bash
# 指定其他端口
ng serve --port 4300
```

### 編譯錯誤
```bash
# 刪除 node_modules 並重新安裝
rm -rf node_modules
npm install
```

### 清除緩存
```bash
# 清除 Angular CLI 緩存
rm -rf .angular
```

## 📦 構建生產版本

### 1. 構建
```bash
ng build --configuration production
```

### 2. 輸出目錄
構建的文件在 `dist/tarot-angular/` 目錄中

### 3. 部署
將 `dist/tarot-angular/` 目錄中的所有文件上傳到您的 Web 服務器。

### 4. 服務器配置
對於 SPA 應用,需要配置服務器將所有請求重定向到 `index.html`:

**Apache (.htaccess)**
```apache
<IfModule mod_rewrite.c>
  RewriteEngine On
  RewriteBase /
  RewriteRule ^index\.html$ - [L]
  RewriteCond %{REQUEST_FILENAME} !-f
  RewriteCond %{REQUEST_FILENAME} !-d
  RewriteRule . /index.html [L]
</IfModule>
```

**Nginx**
```nginx
location / {
  try_files $uri $uri/ /index.html;
}
```

## 🔗 有用的鏈接

- [Angular 官方文檔](https://angular.io/docs)
- [Angular CLI 文檔](https://angular.io/cli)
- [RxJS 文檔](https://rxjs.dev/)
- [TypeScript 文檔](https://www.typescriptlang.org/docs/)

## 📞 需要幫助?

查看以下文檔:
- `README_ZH.md` - 專案完整文檔
- `MIGRATION_GUIDE_ZH.md` - 遷移指南

## ✨ 享受開發!

現在您已經準備好開始開發了!祝您編碼愉快! 🎉


