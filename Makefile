# 專案根目錄 Makefile - 全站 Docker

.PHONY: up down build build-images create-ecr-repos push-ecr push-dockerhub logs backend-logs frontend-logs migrate env

# 確保後端有 .env
env:
	@if [ ! -f backend/.env ]; then cp backend/.env.example backend/.env && echo "Created backend/.env"; fi

# 建置並啟動全部（postgres, redis, backend, frontend）
up: env
	docker compose up -d

# 建置（含 no-cache 可加 make build-no-cache）
build:
	docker compose build

build-no-cache:
	docker compose build --no-cache

# 停止並移除容器
down:
	docker compose down

# 停止並移除容器與 volumes
down-v:
	docker compose down -v

# 查看所有 log
logs:
	docker compose logs -f

# 僅後端 log
backend-logs:
	docker compose logs -f backend

# 僅前端 log
frontend-logs:
	docker compose logs -f frontend

# 後端執行 migrate
migrate:
	docker compose exec backend php artisan migrate --force

# 後端 shell
backend-shell:
	docker compose exec backend sh

# 前端 shell
frontend-shell:
	docker compose exec frontend sh

# ---------- Mac 本機 build 並 push 到 ECR / Docker Hub ----------
# 使用方式見 REGISTRY_DEPLOY.md
IMAGE_TAG ?= latest

# 僅建置 backend、frontend image（不啟動）
build-images:
	docker build -t tarot-diary-backend:$(IMAGE_TAG) ./backend
	docker build -t tarot-diary-frontend:$(IMAGE_TAG) ./frontend

# 建立 ECR 倉庫（若尚未建立）。例：make create-ecr-repos AWS_ACCOUNT_ID=123 AWS_REGION=ap-northeast-1
create-ecr-repos:
	@test -n "$(AWS_ACCOUNT_ID)" || (echo "請設定 AWS_ACCOUNT_ID"; exit 1)
	@test -n "$(AWS_REGION)" || (echo "請設定 AWS_REGION"; exit 1)
	aws ecr create-repository --repository-name tarot-diary-backend --region $(AWS_REGION) 2>/dev/null || true
	aws ecr create-repository --repository-name tarot-diary-frontend --region $(AWS_REGION) 2>/dev/null || true
	@echo "ECR 倉庫已就緒。"

# Push 到 AWS ECR（需先 create-ecr-repos 或手動建立倉庫）
# 例：make push-ecr AWS_ACCOUNT_ID=123456789012 AWS_REGION=ap-northeast-1
ECR_REGISTRY = $(AWS_ACCOUNT_ID).dkr.ecr.$(AWS_REGION).amazonaws.com
push-ecr:
	@test -n "$(AWS_ACCOUNT_ID)" || (echo "請設定 AWS_ACCOUNT_ID"; exit 1)
	@test -n "$(AWS_REGION)" || (echo "請設定 AWS_REGION"; exit 1)
	aws ecr get-login-password --region $(AWS_REGION) | docker login --username AWS --password-stdin $(ECR_REGISTRY)
	docker tag tarot-diary-backend:$(IMAGE_TAG) $(ECR_REGISTRY)/tarot-diary-backend:$(IMAGE_TAG)
	docker tag tarot-diary-frontend:$(IMAGE_TAG) $(ECR_REGISTRY)/tarot-diary-frontend:$(IMAGE_TAG)
	docker push $(ECR_REGISTRY)/tarot-diary-backend:$(IMAGE_TAG)
	docker push $(ECR_REGISTRY)/tarot-diary-frontend:$(IMAGE_TAG)
	@echo "ECR 映像已推送。EC2 上請設定 REGISTRY=$(ECR_REGISTRY) 後 pull/up。"

# Push 到 Docker Hub（需先 docker login）
# 例：make push-dockerhub DOCKERHUB_USERNAME=myuser
push-dockerhub:
	@test -n "$(DOCKERHUB_USERNAME)" || (echo "請設定 DOCKERHUB_USERNAME"; exit 1)
	docker tag tarot-diary-backend:$(IMAGE_TAG) $(DOCKERHUB_USERNAME)/tarot-diary-backend:$(IMAGE_TAG)
	docker tag tarot-diary-frontend:$(IMAGE_TAG) $(DOCKERHUB_USERNAME)/tarot-diary-frontend:$(IMAGE_TAG)
	docker push $(DOCKERHUB_USERNAME)/tarot-diary-backend:$(IMAGE_TAG)
	docker push $(DOCKERHUB_USERNAME)/tarot-diary-frontend:$(IMAGE_TAG)
	@echo "Docker Hub 映像已推送。EC2 上請設定 REGISTRY=$(DOCKERHUB_USERNAME) 後 pull/up。"
