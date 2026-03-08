# 建置階段：Node 18
FROM node:18-alpine AS builder

WORKDIR /app

COPY package.json package-lock.json* ./
RUN npm ci --legacy-peer-deps 2>/dev/null || npm install --legacy-peer-deps

COPY . .
RUN npm run build

# 執行階段：nginx
FROM nginx:alpine

# 複製建置結果（Angular 預設輸出為 dist/tarot-angular）
COPY --from=builder /app/dist/tarot-angular /usr/share/nginx/html

# 使用可替換變數的設定範本，runtime 用 envsubst 替換 BACKEND_URL
COPY nginx.conf.template /etc/nginx/templates/default.conf.template

ENV BACKEND_URL=http://app:8000

# Alpine 的 nginx 映像使用 /docker-entrypoint.d/；我們用 shell 做 envsubst 後啟動 nginx
RUN apk add --no-cache gettext

COPY docker-entrypoint.sh /docker-entrypoint.sh
RUN chmod +x /docker-entrypoint.sh

EXPOSE 80

ENTRYPOINT ["/docker-entrypoint.sh"]
CMD ["nginx", "-g", "daemon off;"]
