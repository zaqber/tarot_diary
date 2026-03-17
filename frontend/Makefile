# Frontend Makefile - Tarot Diary Angular

.PHONY: build up down logs shell image run-standalone

# 建置 image
build:
	docker compose build

# 建置（不快取）
build-no-cache:
	docker compose build --no-cache

# 啟動容器（port 4200 -> 80）
up:
	docker compose up -d

# 建置並啟動
build-up:
	docker compose build && docker compose up -d

# 停止並移除容器
down:
	docker compose down

# 查看 log
logs:
	docker compose logs -f frontend

# 進入容器 shell
shell:
	docker compose exec frontend sh

# 僅建置 image，不啟動
image:
	docker compose build frontend

# 單獨運行（API 轉發到 host 的後端，Mac/Windows）
run-standalone:
	docker compose run --rm -p 4200:80 -e BACKEND_URL=http://host.docker.internal:8000 frontend

# --- AWS ECR 打包上傳（--platform linux/amd64 供 EC2 跑，需 Docker Buildx）---
ECR_URI = 636155394566.dkr.ecr.ap-northeast-1.amazonaws.com/tarot-diary/frontend
ECR_REGION = ap-northeast-1

ecr-login:
	aws ecr get-login-password --region $(ECR_REGION) | docker login --username AWS --password-stdin 636155394566.dkr.ecr.ap-northeast-1.amazonaws.com

ecr-build:
	docker build --platform linux/amd64 -t $(ECR_URI):latest .

ecr-push:
	docker push $(ECR_URI):latest

ecr-push-v001:
	docker tag $(ECR_URI):latest $(ECR_URI):v0.0.1
	docker push $(ECR_URI):v0.0.1

ecr: ecr-build ecr-login ecr-push
