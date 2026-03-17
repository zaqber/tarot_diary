# Backend Makefile - Tarot Diary Laravel API

.PHONY: build up down logs shell migrate fresh install env

# 預設：僅啟動 DB 與 Redis（本機用 php artisan serve 開發時使用）
up-db:
	docker compose up -d postgres redis

# 啟動全部（含 app 容器）
up:
	docker compose up -d

# 建置並啟動（背景）
build:
	docker compose build
	docker compose up -d

# 建置（不快取）
build-no-cache:
	docker compose build --no-cache

# 停止並移除容器
down:
	docker compose down

# 停止並移除容器、volumes
down-v:
	docker compose down -v

# 查看 log（app）
logs:
	docker compose logs -f app

# 查看所有服務 log
logs-all:
	docker compose logs -f

# 進入 app 容器 shell
shell:
	docker compose exec app sh

# 在 app 容器內執行 migrate
migrate:
	docker compose exec app php artisan migrate --force

# 在 app 容器內執行 migrate:fresh（清空並重建）
fresh:
	docker compose exec app php artisan migrate:fresh --force

# 確保有 .env（從範例複製）
env:
	@if [ ! -f .env ]; then cp .env.example .env && echo "Created .env from .env.example"; else echo ".env already exists"; fi

# 僅建置 image，不啟動
image:
	docker compose build app

# 重啟 app 服務
restart:
	docker compose restart app

# --- AWS ECR 打包上傳（--platform linux/amd64 供 EC2 跑，需 Docker Buildx）---
ECR_URI = 636155394566.dkr.ecr.ap-northeast-1.amazonaws.com/tarot-diary/backend
ECR_REGION = ap-northeast-1

# 登入 ECR（需已設定 AWS CLI 且具 ECR 權限）
ecr-login:
	aws ecr get-login-password --region $(ECR_REGION) | docker login --username AWS --password-stdin 636155394566.dkr.ecr.ap-northeast-1.amazonaws.com

# 建置並打 tag（供 ECR 用，指定 linux/amd64 供 EC2 跑）
ecr-build:
	docker build --platform linux/amd64 -t $(ECR_URI):latest .

# 推送到 ECR（先執行 make ecr-login 與 make ecr-build）
ecr-push:
	docker push $(ECR_URI):latest

# 推送 v0.0.1（先 ecr-build 後執行，與 latest 同映像）
ecr-push-v001:
	docker tag $(ECR_URI):latest $(ECR_URI):v0.0.1
	docker push $(ECR_URI):v0.0.1

# 一鍵：建置 + 登入 + 推送 latest（要 v0.0.1 再執行 make ecr-push-v001）
ecr: ecr-build ecr-login ecr-push
