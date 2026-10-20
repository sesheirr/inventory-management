.PHONY: help dev run webhook deploy tunnel lint up down build install test artisan composer bun migrate migrate-fresh seed bash sh logs clean restart db-status


# Default goal
.DEFAULT_GOAL := help

# Colors for terminal styling
COLOR_RESET   = \033[0m
COLOR_INFO    = \033[36m
COLOR_SUCCESS = \033[32m
COLOR_WARNING = \033[33m
COLOR_TITLE   = \033[1;34m

help: ## Show this help documentation
	@echo ""
	@echo "$(COLOR_TITLE)Inventory Management - Docker, Local, Webhook & Deploy Commands$(COLOR_RESET)"
	@echo "=================================================================="
	@awk 'BEGIN {FS = ":.*?## "} /^[a-zA-Z0-9_:-]+:.*?## / {printf "  $(COLOR_INFO)%-15s$(COLOR_RESET) %s\n", $$1, $$2}' $(MAKEFILE_LIST) 2>/dev/null || echo "  (Gunakan 'make run', 'make dev', 'make use-db:local', 'make use-db:live', dll)"
	@echo ""
	@echo "$(COLOR_WARNING)Example:$(COLOR_RESET)"
	@echo "  make run             # Magic all-in-one local runner + Webhook Server (NO Docker)"
	@echo "  make use-db:local    # Switch active DB to local MySQL (Laragon/127.0.0.1)"
	@echo "  make use-db:live     # Switch active DB to live MySQL (Aiven/Cloud)"
	@echo "  make dev             # Start Laravel + Bun Vite + Webhook Server inside Docker"
	@echo "  make deploy          # Run automated production deployment script (scripts/deploy.sh)"
	@echo "  make tunnel          # Expose app via Cloudflare Free HTTPS (*.trycloudflare.com)"
	@echo "  make install         # One-time Docker project setup"
	@echo "  make artisan c=\"route:list\""
	@echo ""

use-db\:local: ## Switch active database to local MySQL (127.0.0.1:3306)
	@php artisan db:use local

use-db-local: use-db\:local

use-db\:live: ## Switch active database to live MySQL cloud (Aiven/VPS)
	@php artisan db:use live

use-db-live: use-db\:live

db-status: ## Show current database status and verify connection
	@php artisan db:show


run: ## Magic all-in-one local runner + Webhook Server WITHOUT Docker (auto-detects .env & smart diagnostics)
	@bash ./docker/run-local.sh


webhook: ## Start standalone Webhook Broker (:8085) & Realtime Queue Worker inside Docker
	@docker compose run --rm --service-ports app php artisan inventory:webhook-server --host=0.0.0.0 --port=8085

deploy: ## Automated production deployment (migrate, build via Bun, optimize cache, restart workers)
	@bash ./scripts/deploy.sh

tunnel: ## Smart Cloudflare Tunnel (auto-detects Local or Docker & releases domain on Ctrl+C)
	@bash ./docker/run-tunnel.sh

tunnel\:local: ## Force Cloudflare Tunnel in Local mode
	@bash ./docker/run-tunnel.sh --local

tunnel\:docker: ## Force Cloudflare Tunnel in Docker mode
	@bash ./docker/run-tunnel.sh --docker

lan-https: ## Start Local LAN HTTPS Proxy (:8443) + Root CA generator for Mobile Chrome PWA
	@bun scripts/lan-https-proxy.mjs

lint: ## Check PHP syntax, Laravel Pint formatting, TypeScript typecheck, and frontend production build
	@docker compose run --rm app sh -c "find app bootstrap config database routes tests -name '*.php' -print0 | xargs -0 -n1 php -l >/dev/null && ./vendor/bin/pint --test && bun run typecheck && bun run build"

install: build ## Setup project completely via Docker (env, deps, db, build)
	@echo "$(COLOR_INFO)Checking .env configuration...$(COLOR_RESET)"
	@test -f .env || cp .env.example .env
	@echo "$(COLOR_INFO)Starting MySQL container in background...$(COLOR_RESET)"
	@docker compose up -d db
	@echo "$(COLOR_INFO)Installing Composer dependencies...$(COLOR_RESET)"
	@docker compose run --rm app composer install --no-interaction --prefer-dist
	@echo "$(COLOR_INFO)Installing Bun frontend dependencies...$(COLOR_RESET)"
	@docker compose run --rm app bun install
	@echo "$(COLOR_INFO)Generating Application Key...$(COLOR_RESET)"
	@docker compose run --rm app php artisan key:generate --ansi
	@echo "$(COLOR_INFO)Preparing database & running Smart DB Sync (migrations + seeders)...$(COLOR_RESET)"
	@docker compose run --rm app sh -c "DB_HOST=\$$(php -r '\$$e=@parse_ini_file(\".env\"); \$$h=\$$e[\"DB_HOST\"]??\"db\"; if(\$$h===\"127.0.0.1\"||\$$h===\"localhost\"){try{new PDO(\"mysql:host=host.docker.internal;port=\".(\$$e[\"DB_PORT\"]??3306),\$$e[\"DB_USERNAME\"]??\"root\",\$$e[\"DB_PASSWORD\"]??\"\",[PDO::ATTR_TIMEOUT=>2]);echo \"host.docker.internal\";}catch(Throwable \$$x){echo \"db\";}}else{echo \$$h;}') php artisan inventory:smart-db-sync --ansi || (DB_HOST=db DB_PORT=3306 DB_DATABASE=inventory DB_USERNAME=inventory DB_PASSWORD=secret php artisan inventory:smart-db-sync --ansi)"
	@echo "$(COLOR_INFO)Building frontend assets...$(COLOR_RESET)"
	@docker compose run --rm app bun run build
	@echo "$(COLOR_SUCCESS)Project installation complete! Run 'make dev' or 'make run' to start development.$(COLOR_RESET)"

build: ## Build or rebuild Docker images
	@echo "$(COLOR_INFO)Building Docker images...$(COLOR_RESET)"
	@docker compose build

dev: ## Start Laravel (:8000) + LAN HTTPS (:8443) + Bun Vite (:5173) + Webhook Server (:8085) in Docker foreground
	@echo "$(COLOR_SUCCESS)Starting Laravel + LAN HTTPS + Bun Vite + Webhook Server development environment...$(COLOR_RESET)"
	@docker compose up -d db
	@docker compose run --rm --service-ports app bash ./docker/run-dev.sh

up: ## Start containers in the background (detached mode)
	@echo "$(COLOR_SUCCESS)Starting containers in background...$(COLOR_RESET)"
	@docker compose up -d

down: ## Stop and remove all containers
	@echo "$(COLOR_WARNING)Stopping containers...$(COLOR_RESET)"
	@docker compose down

restart: down up ## Restart all background containers

logs: ## Tail container logs in real time
	@docker compose logs -f app

bash: ## Open an interactive bash shell in the app container
	@docker compose exec -it app bash

sh: ## Open an interactive sh shell in the app container
	@docker compose exec -it app sh

artisan: ## Run an artisan command (usage: make artisan c="route:list")
	@docker compose run --rm app php artisan $(c)

composer: ## Run a composer command (usage: make composer c="require vendor/package")
	@docker compose run --rm app composer $(c)

bun: ## Run a bun command (usage: make bun c="add package")
	@docker compose run --rm app bun $(c)

migrate: ## Run database migrations
	@docker compose run --rm app php artisan migrate

migrate-fresh: ## Rollback all migrations and run them again
	@docker compose run --rm app php artisan migrate:fresh --seed

seed: ## Run database seeders
	@docker compose run --rm app php artisan db:seed --force

test: ## Run test suite inside container (PHPUnit / Pest)
	@docker compose run --rm -e APP_ENV=testing app php artisan test

clean: ## Clear compiled views, cache, route, and config
	@docker compose run --rm app php artisan optimize:clear
	@rm -f public/hot

