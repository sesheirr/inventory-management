.PHONY: help setup doctor dev run webhook deploy tunnel tunnel-local tunnel-docker lan-https lint up down build install test artisan composer bun migrate migrate-fresh seed bash sh logs clean restart db-status use-db-local use-db-live use-db-sqlite

# Default goal
.DEFAULT_GOAL := help

# Thin wrapper: delegate cross-platform tasks to scripts/dev-cli.mjs (Bun or Node.js)
JS_RT := $(shell command -v bun 2>/dev/null || command -v node 2>/dev/null)
DEV_CLI := $(if $(JS_RT),$(JS_RT) scripts/dev-cli.mjs,)

# Colors for terminal styling
COLOR_RESET   = \033[0m
COLOR_INFO    = \033[36m
COLOR_SUCCESS = \033[32m
COLOR_WARNING = \033[33m
COLOR_TITLE   = \033[1;34m

help: ## Show this help documentation
	@echo ""
	@echo "$(COLOR_TITLE)Inventory Management - Cross-Platform Dev Commands (Thin Makefile Alias)$(COLOR_RESET)"
	@echo "=========================================================================="
	@awk 'BEGIN {FS = ":.*?## "} /^[a-zA-Z0-9_:-]+:.*?## / {printf "  $(COLOR_INFO)%-16s$(COLOR_RESET) %s\n", $$1, $$2}' $(MAKEFILE_LIST) 2>/dev/null || true
	@echo ""
	@echo "$(COLOR_WARNING)Tip (Windows / Cross-Platform without Make):$(COLOR_RESET)"
	@echo "  Windows : .\\onboarding.bat  |  .\\dev.bat  |  bun run start  |  npm run start"
	@echo "  Unix    : ./setup.sh        |  make run  |  make dev"
	@echo ""

setup: ## Guided cross-platform setup (Local or Docker)
	@if [ -n "$(DEV_CLI)" ]; then $(DEV_CLI) setup; else ./setup.sh; fi

doctor: ## Show local/Docker toolchain & .env diagnostics
	@if [ -n "$(DEV_CLI)" ]; then $(DEV_CLI) doctor; else echo "Install Bun or Node.js, or run ./setup.sh"; fi

use-db\:local: ## Switch active database to local MySQL (127.0.0.1:3306)
	@if [ -n "$(DEV_CLI)" ]; then $(DEV_CLI) db:local; else php artisan db:use local; fi

use-db-local: use-db\:local

use-db\:live: ## Switch active database to live MySQL cloud (Aiven/VPS)
	@if [ -n "$(DEV_CLI)" ]; then $(DEV_CLI) db:live; else php artisan db:use live; fi

use-db-live: use-db\:live

use-db\:sqlite: ## Switch active database to zero-config SQLite
	@if [ -n "$(DEV_CLI)" ]; then $(DEV_CLI) db:sqlite; else echo "Use bun/npm run db:sqlite"; fi

use-db-sqlite: use-db\:sqlite

db-status: ## Show current database status and verify connection
	@if [ -n "$(DEV_CLI)" ]; then $(DEV_CLI) db:status; else php artisan db:show; fi

run: ## Start local runner (Laravel + Vite + Webhook + LAN HTTPS, NO Docker)
	@if [ -n "$(DEV_CLI)" ]; then $(DEV_CLI) run; else bash ./docker/run-local.sh; fi

webhook: ## Start standalone Webhook Broker (:8085) & Realtime Queue Worker inside Docker
	@docker compose run --rm --service-ports app php artisan inventory:webhook-server --host=0.0.0.0 --port=8085

deploy: ## Automated production deployment (migrate, build, optimize cache, restart workers)
	@bash ./scripts/deploy.sh

tunnel: ## Smart Cloudflare Tunnel (auto-detects Local or Docker)
	@if [ -n "$(DEV_CLI)" ]; then $(DEV_CLI) tunnel; else bash ./docker/run-tunnel.sh; fi

tunnel\:local: ## Force Cloudflare Tunnel in Local mode
	@if [ -n "$(DEV_CLI)" ]; then $(DEV_CLI) tunnel --local; else bash ./docker/run-tunnel.sh --local; fi

tunnel-local: tunnel\:local

tunnel\:docker: ## Force Cloudflare Tunnel in Docker mode
	@if [ -n "$(DEV_CLI)" ]; then $(DEV_CLI) tunnel --docker; else bash ./docker/run-tunnel.sh --docker; fi

tunnel-docker: tunnel\:docker

lan-https: ## Start Local LAN HTTPS Proxy (:8443) + Root CA generator for Mobile Chrome PWA
	@if [ -n "$(JS_RT)" ]; then $(JS_RT) scripts/lan-https-proxy.mjs; else echo "Requires Bun or Node.js"; exit 1; fi

lint: ## Check PHP syntax, Laravel Pint formatting, TypeScript typecheck, and Vite build
	@if [ -n "$(DEV_CLI)" ]; then $(DEV_CLI) lint; else docker compose run --rm app sh -c "find app bootstrap config database routes tests -name '*.php' -print0 | xargs -0 -n1 php -l >/dev/null && ./vendor/bin/pint --test && bun run typecheck && bun run build"; fi

install: ## Setup project completely via Docker (env, deps, db, build)
	@if [ -n "$(DEV_CLI)" ]; then $(DEV_CLI) setup --docker; else \
		docker compose build && \
		(test -f .env || cp .env.example .env) && \
		docker compose up -d db && \
		docker compose run --rm app composer install --no-interaction --prefer-dist && \
		docker compose run --rm app bun install && \
		docker compose run --rm app php artisan key:generate --ansi && \
		docker compose run --rm app php artisan inventory:smart-db-sync --ansi && \
		docker compose run --rm app bun run build; \
	fi

build: ## Build or rebuild Docker images
	@docker compose build

dev: ## Start Laravel (:8000) + LAN HTTPS (:8443) + Vite (:5173) + Webhook (:8085) in Docker
	@if [ -n "$(DEV_CLI)" ]; then $(DEV_CLI) dev; else docker compose up -d db && docker compose run --rm --service-ports app bash ./docker/run-dev.sh; fi

up: ## Start containers in the background (detached mode)
	@docker compose up -d

down: ## Stop and remove all containers
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

test: ## Run test suite (local or Docker)
	@if [ -n "$(DEV_CLI)" ]; then $(DEV_CLI) test; else docker compose run --rm -e APP_ENV=testing app php artisan test; fi

clean: ## Clear compiled views, cache, route, config, and public/hot
	@if [ -n "$(DEV_CLI)" ]; then $(DEV_CLI) clean; else docker compose run --rm app php artisan optimize:clear && rm -f public/hot; fi
