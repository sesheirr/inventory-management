.PHONY: help dev run tunnel lint up down build install test artisan composer bun migrate migrate-fresh seed bash sh logs clean restart

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
	@echo "$(COLOR_TITLE)Inventory Management - Docker, Local & Tunnel Commands$(COLOR_RESET)"
	@echo "=========================================================="
	@awk 'BEGIN {FS = ":.*?## "} /^[a-zA-Z_-]+:.*?## / {printf "  $(COLOR_INFO)%-15s$(COLOR_RESET) %s\n", $$1, $$2}' $(MAKEFILE_LIST)
	@echo ""
	@echo "$(COLOR_WARNING)Example:$(COLOR_RESET)"
	@echo "  make run             # Magic all-in-one local runner (NO Docker, auto-detects .env)"
	@echo "  make dev             # Start Laravel + Bun Vite dev servers inside Docker"
	@echo "  make tunnel          # Expose app via Cloudflare Free HTTPS (*.trycloudflare.com)"
	@echo "  make install         # One-time Docker project setup"
	@echo "  make artisan c=\"route:list\""
	@echo ""

run: ## Magic all-in-one local runner WITHOUT Docker (auto-detects .env & smart diagnostics)
	@bash ./docker/run-local.sh

tunnel: ## Expose local/Docker server via Cloudflare Free HTTPS (*.trycloudflare.com) for remote CRUD testing
	@bash ./docker/run-tunnel.sh

lint: ## Check PHP syntax, Laravel Pint formatting, and frontend production build
	@docker compose run --rm app sh -c "find app bootstrap config database routes tests -name '*.php' -print0 | xargs -0 -n1 php -l >/dev/null && ./vendor/bin/pint --test && bun run build"

install: build ## Setup project completely via Docker (env, deps, db, build)
	@echo "$(COLOR_INFO)Checking .env configuration...$(COLOR_RESET)"
	@test -f .env || cp .env.example .env
	@echo "$(COLOR_INFO)Installing Composer dependencies...$(COLOR_RESET)"
	@docker compose run --rm app composer install --no-interaction --prefer-dist
	@echo "$(COLOR_INFO)Installing Bun frontend dependencies...$(COLOR_RESET)"
	@docker compose run --rm app bun install
	@echo "$(COLOR_INFO)Generating Application Key...$(COLOR_RESET)"
	@docker compose run --rm app php artisan key:generate --ansi
	@echo "$(COLOR_INFO)Preparing database & running migrations...$(COLOR_RESET)"
	@docker compose run --rm app sh -c "mkdir -p database && touch database/database.sqlite && chmod 666 database/database.sqlite 2>/dev/null || true"
	@docker compose run --rm app php artisan migrate --force
	@echo "$(COLOR_INFO)Seeding initial database...$(COLOR_RESET)"
	@docker compose run --rm app php artisan db:seed --force
	@echo "$(COLOR_INFO)Building frontend assets...$(COLOR_RESET)"
	@docker compose run --rm app bun run build
	@echo "$(COLOR_SUCCESS)Project installation complete! Run 'make dev' or 'make run' to start development.$(COLOR_RESET)"

build: ## Build or rebuild Docker images
	@echo "$(COLOR_INFO)Building Docker images...$(COLOR_RESET)"
	@docker compose build

dev: ## Start Laravel (port 8000) + Bun Vite (port 5173) in Docker foreground
	@echo "$(COLOR_SUCCESS)Starting development environment...$(COLOR_RESET)"
	@docker compose up app

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

