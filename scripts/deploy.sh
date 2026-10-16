#!/usr/bin/env bash
# ==============================================================================
# SIM-Inventaris Diskominfo Kabupaten Garut — Production Deployment Script (v2)
# ==============================================================================
# Architect & Lead Contributor : fwzann-abd (https://github.com/fwzann-abd)
# Co-Collaborator              : msayib (https://github.com/msayib)
# Package Manager Policy       : STRICTLY BUN ONLY (never npm)
#
# Usage:
#   ./scripts/deploy.sh                   # Auto-detect (Native PHP/Bun or Docker)
#   ./scripts/deploy.sh --docker          # Force deployment via Docker Compose
#   ./scripts/deploy.sh --branch=v2       # Specify git branch to pull (default: v2)
#   ./scripts/deploy.sh --skip-git        # Deploy current working tree without git pull
#   ./scripts/deploy.sh --dry-run         # Validate environment & print steps only
# ==============================================================================

set -euo pipefail

PROJECT_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$PROJECT_ROOT"

BRANCH="v2"
USE_DOCKER="auto"
SKIP_GIT=0
DRY_RUN=0

for arg in "$@"; do
    case "$arg" in
        --docker) USE_DOCKER="yes" ;;
        --native) USE_DOCKER="no" ;;
        --branch=*) BRANCH="${arg#*=}" ;;
        --skip-git) SKIP_GIT=1 ;;
        --dry-run) DRY_RUN=1 ;;
        -h|--help)
            echo "Usage: ./scripts/deploy.sh [--docker|--native] [--branch=v2] [--skip-git] [--dry-run]"
            exit 0
            ;;
    esac
done

COLOR_RESET="\033[0m"
COLOR_INFO="\033[36m"
COLOR_SUCCESS="\033[32m"
COLOR_WARN="\033[33m"
COLOR_BOLD="\033[1m"

log_step() {
    echo -e "${COLOR_INFO}${COLOR_BOLD}==> $1${COLOR_RESET}"
}

log_ok() {
    echo -e "${COLOR_SUCCESS}✔ $1${COLOR_RESET}"
}

run_cmd() {
    if [[ "$DRY_RUN" -eq 1 ]]; then
        echo "  [dry-run] $*"
    else
        "$@"
    fi
}

if [[ "$USE_DOCKER" == "auto" ]]; then
    if command -v php >/dev/null 2>&1 && command -v composer >/dev/null 2>&1 && command -v bun >/dev/null 2>&1; then
        USE_DOCKER="no"
    else
        USE_DOCKER="yes"
    fi
fi

echo -e "${COLOR_BOLD}======================================================================${COLOR_RESET}"
echo -e "${COLOR_BOLD} 🚀 SIM-Inventaris Diskominfo Garut — Production Deployment (v2)${COLOR_RESET}"
echo -e " 📌 Target Branch : ${BRANCH}"
echo -e " ⚙️  Execution Mode: $([[ "$USE_DOCKER" == "yes" ]] && echo "Docker Compose" || echo "Native Host (PHP 8.3 + Bun)")"
echo -e "${COLOR_BOLD}======================================================================${COLOR_RESET}"

# 1. Ensure .env exists
log_step "1/8 Memeriksa konfigurasi environment (.env)..."
if [[ ! -f .env ]]; then
    run_cmd cp .env.example .env
fi
log_ok "Konfigurasi .env tersedia."

# 2. Git Pull latest release (unless --skip-git)
if [[ "$SKIP_GIT" -eq 0 ]]; then
    log_step "2/8 Menarik pembaruan kode terbaru dari origin/${BRANCH}..."
    run_cmd git fetch origin "$BRANCH"
    run_cmd git checkout "$BRANCH"
    run_cmd git pull --ff-only origin "$BRANCH"
    log_ok "Source code tersinkronisasi dengan origin/${BRANCH}."
else
    log_step "2/8 Melewati git pull (--skip-git aktif)..."
fi

if [[ "$USE_DOCKER" == "yes" ]]; then
    log_step "3/8 Membangun image & menyalakan layanan Docker..."
    run_cmd docker compose build app
    run_cmd docker compose up -d

    log_step "4/8 Menginstal dependensi PHP (Composer)..."
    run_cmd docker compose exec -T app composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist

    log_step "5/8 Menginstal dependensi Frontend & membangun bundle produksi via Bun..."
    run_cmd docker compose exec -T app bun install --frozen-lockfile
    run_cmd docker compose exec -T app bun run build

    log_step "6/8 Menjalankan migrasi database & storage symlink..."
    run_cmd docker compose exec -T app php artisan migrate --force
    run_cmd docker compose exec -T app php artisan storage:link --force || true

    log_step "7/8 Mengoptimalkan cache konfigurasi, route, view & event Laravel..."
    run_cmd docker compose exec -T app php artisan optimize:clear
    run_cmd docker compose exec -T app php artisan config:cache
    run_cmd docker compose exec -T app php artisan route:cache
    run_cmd docker compose exec -T app php artisan view:cache

    log_step "8/8 Merestart Queue & Webhook Server worker..."
    run_cmd docker compose exec -T app php artisan queue:restart
else
    log_step "3/8 Memasuki mode pemeliharaan (Maintenance Mode)..."
    run_cmd php artisan down --retry=15 || true

    log_step "4/8 Menginstal dependensi PHP (Composer)..."
    run_cmd composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist

    log_step "5/8 Menginstal dependensi Frontend & membangun bundle produksi via Bun..."
    run_cmd bun install --frozen-lockfile
    run_cmd bun run build

    log_step "6/8 Menjalankan migrasi database & storage symlink..."
    run_cmd php artisan migrate --force
    run_cmd php artisan storage:link --force || true

    log_step "7/8 Mengoptimalkan cache konfigurasi, route, view & event Laravel..."
    run_cmd php artisan optimize:clear
    run_cmd php artisan config:cache
    run_cmd php artisan route:cache
    run_cmd php artisan view:cache

    log_step "8/8 Merestart Queue/Webhook Worker & membuka kembali aplikasi..."
    run_cmd php artisan queue:restart
    run_cmd php artisan up
fi

echo ""
echo -e "${COLOR_SUCCESS}${COLOR_BOLD}======================================================================${COLOR_RESET}"
echo -e "${COLOR_SUCCESS}${COLOR_BOLD} ✅ Deployment SIM-Inventaris Diskominfo Garut (v2) Selesai!${COLOR_RESET}"
echo -e "${COLOR_SUCCESS}${COLOR_BOLD}======================================================================${COLOR_RESET}"
