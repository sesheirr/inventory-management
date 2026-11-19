#!/usr/bin/env sh
# ==============================================================================
# SIM-Inventaris Diskominfo Garut — Cross-Platform Setup Bootstrapper (macOS/Linux/Git Bash)
# Works even on a fresh machine before Bun, Node.js, or Make is installed.
# ==============================================================================

set -eu

SCRIPT_DIR="$(CDPATH= cd -- "$(dirname -- "$0")" && pwd)"
cd "$SCRIPT_DIR"

# Include common user bin paths (Bun, Herd, Homebrew)
for p in "$HOME/.bun/bin" "$HOME/Library/Application Support/Herd/bin" "/opt/homebrew/bin" "/usr/local/bin"; do
    if [ -d "$p" ]; then
        case ":$PATH:" in
            *":$p:"*) ;;
            *) PATH="$p:$PATH" ;;
        esac
    fi
done
export PATH

find_js_rt() {
    if command -v bun >/dev/null 2>&1; then
        command -v bun
        return 0
    fi
    if command -v node >/dev/null 2>&1; then
        command -v node
        return 0
    fi
    return 1
}

echo ""
echo "======================================================================"
echo "  SIM-Inventaris Diskominfo Garut — Smart Setup (macOS / Linux)"
echo "======================================================================"

JS_RT="$(find_js_rt || true)"

if [ -n "$JS_RT" ]; then
    HAS_START=1
    for arg in "$@"; do
        case "$arg" in
            --check-only|--no-start) HAS_START=0 ;;
        esac
    done
    if [ "$HAS_START" = "1" ]; then
        exec "$JS_RT" scripts/dev-cli.mjs setup --start "$@"
    else
        exec "$JS_RT" scripts/dev-cli.mjs setup "$@"
    fi
fi

echo ""
echo "⚠️  Bun dan Node.js belum terdeteksi di sistem Anda."

# If Docker is already running, allow zero-host-dependency Docker setup directly
if command -v docker >/dev/null 2>&1 && docker info >/dev/null 2>&1; then
    echo "✅ Docker Daemon terdeteksi aktif!"
    printf "   Jalankan setup & start via Docker tanpa install Node/Bun di host? [Y/n]: "
    read -r ans || ans="Y"
    case "${ans:-Y}" in
        [Nn]*) ;;
        *)
            [ -f .env ] || cp .env.example .env
            docker compose build
            docker compose up -d db
            docker compose run --rm app composer install --no-interaction --prefer-dist
            docker compose run --rm app bun install
            docker compose run --rm app php artisan key:generate --ansi
            docker compose run --rm app php artisan inventory:smart-db-sync --ansi
            docker compose run --rm app bun run build
            exec docker compose run --rm --service-ports app bash ./docker/run-dev.sh
            ;;
    esac
fi

echo ""
echo "Pilih cara instalasi runtime JS untuk melanjutkan setup:"
echo "  [1] Install Bun (Direkomendasikan — cepat & ringan)"
echo "  [2] Install Node.js LTS (via Homebrew / nvm / paket OS)"
echo "  [3] Keluar & install manual"
printf "Pilihan Anda [default: 1]: "
read -r choice || choice="1"

case "${choice:-1}" in
    2)
        if [ "$(uname -s)" = "Darwin" ] && command -v brew >/dev/null 2>&1; then
            brew install node
        else
            echo "Silakan install Node.js LTS dari https://nodejs.org/ lalu jalankan ./setup.sh kembali."
            exit 1
        fi
        ;;
    3)
        exit 0
        ;;
    *)
        if ! command -v curl >/dev/null 2>&1; then
            echo "❌ curl tidak ditemukan. Silakan install curl atau Node.js secara manual."
            exit 1
        fi
        curl -fsSL https://bun.sh/install | bash
        PATH="$HOME/.bun/bin:$PATH"
        export PATH
        ;;
esac

JS_RT="$(find_js_rt || true)"
if [ -z "$JS_RT" ]; then
    echo "❌ Gagal menemukan Bun atau Node.js setelah instalasi. Buka terminal baru dan jalankan ./setup.sh lagi."
    exit 1
fi

exec "$JS_RT" scripts/dev-cli.mjs setup --start "$@"
