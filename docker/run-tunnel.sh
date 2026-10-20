#!/usr/bin/env bash
# ==============================================================================
# Smart Cloudflare Free HTTPS Quick Tunnel Runner — Inventaris Diskominfo Garut
# Smart-detects Local vs Docker mode, auto-provisions cloudflared if needed,
# and cleanly kills the tunnel process/container on Ctrl+C / Cmd+C to release
# the *.trycloudflare.com domain immediately.
# ==============================================================================

set -u

COLOR_RESET="\033[0m"
COLOR_INFO="\033[36m"
COLOR_SUCCESS="\033[32m"
COLOR_WARNING="\033[33m"
COLOR_ERROR="\033[31m"
COLOR_BOLD="\033[1m"

PORT="${PORT:-8000}"
MODE="${TUNNEL_MODE:-auto}"

for arg in "$@"; do
    case "$arg" in
        --local|local)
            MODE="local"
            ;;
        --docker|docker)
            MODE="docker"
            ;;
    esac
done

TARGET_URL="http://127.0.0.1:${PORT}"
STARTED_BG_SERVER=0
BG_SERVER_PID=""
TUNNEL_PID=""
TAIL_PID=""
CF_CONTAINER_NAME="inventory_cf_tunnel_${PORT}"
TUNNEL_LOG_FILE=""
CLEANED_UP=0

echo -e "${COLOR_INFO}${COLOR_BOLD}======================================================================${COLOR_RESET}"
echo -e "${COLOR_INFO}${COLOR_BOLD} 🌐 Smart Cloudflare HTTPS Tunnel — Inventaris Diskominfo Kab. Garut${COLOR_RESET}"
echo -e "${COLOR_INFO}${COLOR_BOLD}======================================================================${COLOR_RESET}"

is_docker_daemon_running() {
    command -v docker >/dev/null 2>&1 && docker info >/dev/null 2>&1
}

is_docker_app_running() {
    is_docker_daemon_running && docker ps --format '{{.Names}}' 2>/dev/null | grep -Eq 'inventory_app|app'
}

is_server_listening() {
    if command -v curl >/dev/null 2>&1; then
        curl -s -o /dev/null --max-time 2 "${TARGET_URL}/up" || curl -s -o /dev/null --max-time 2 "${TARGET_URL}"
        return $?
    elif command -v nc >/dev/null 2>&1; then
        nc -z 127.0.0.1 "${PORT}" >/dev/null 2>&1
        return $?
    fi
    return 1
}

cleanup() {
    if [ "$CLEANED_UP" = "1" ]; then
        return
    fi
    CLEANED_UP=1

    echo ""
    echo -e "${COLOR_WARNING}🛑 Menutup Cloudflare HTTPS Tunnel & melepaskan domain *.trycloudflare.com...${COLOR_RESET}"

    if [ -n "$TAIL_PID" ]; then
        kill "$TAIL_PID" 2>/dev/null || true
    fi

    if [ -n "$TUNNEL_PID" ]; then
        # Send SIGINT/SIGTERM first so cloudflared unregisters the quick tunnel cleanly
        kill -INT "$TUNNEL_PID" 2>/dev/null || kill -TERM "$TUNNEL_PID" 2>/dev/null || true
        if command -v taskkill >/dev/null 2>&1; then
            taskkill //F //T //PID "$TUNNEL_PID" >/dev/null 2>&1 || true
        fi
        sleep 0.5
        kill -9 "$TUNNEL_PID" 2>/dev/null || true
    fi

    if command -v docker >/dev/null 2>&1; then
        docker rm -f "$CF_CONTAINER_NAME" >/dev/null 2>&1 || true
    fi

    if [ "$STARTED_BG_SERVER" = "1" ] && [ -n "$BG_SERVER_PID" ]; then
        echo -e "${COLOR_INFO}🧹 Menghentikan server latar belakang lokal (PID ${BG_SERVER_PID})...${COLOR_RESET}"
        kill "$BG_SERVER_PID" 2>/dev/null || true
        if command -v taskkill >/dev/null 2>&1; then
            taskkill //F //T //PID "$BG_SERVER_PID" >/dev/null 2>&1 || true
        fi
    elif [ "$STARTED_BG_SERVER" = "2" ] && is_docker_daemon_running; then
        echo -e "${COLOR_INFO}🧹 Menghentikan container app sementara...${COLOR_RESET}"
        docker compose stop app >/dev/null 2>&1 || true
    fi

    if [ -n "$TUNNEL_LOG_FILE" ] && [ -f "$TUNNEL_LOG_FILE" ]; then
        rm -f "$TUNNEL_LOG_FILE" 2>/dev/null || true
    fi

    echo -e "${COLOR_SUCCESS}✅ Tunnel berhasil dihentikan & domain Cloudflare telah dilepas.${COLOR_RESET}"
    exit 0
}
trap cleanup SIGINT SIGTERM EXIT

# ------------------------------------------------------------------------------
# 1. Ensure Production Frontend Assets Exist for Remote HTTPS Clients
# ------------------------------------------------------------------------------
if [ ! -f public/build/manifest.json ]; then
    echo -e "${COLOR_INFO}🎨 Memastikan aset produksi (public/build) siap untuk akses HTTPS jarak jauh...${COLOR_RESET}"
    if command -v bun >/dev/null 2>&1 && [ -d node_modules ]; then
        bun run build
    elif command -v npm >/dev/null 2>&1 && [ -d node_modules ]; then
        npm run build
    elif is_docker_daemon_running; then
        docker compose run --rm app bun run build
    fi
fi

# ------------------------------------------------------------------------------
# 2. Smart Detect Server Mode (Local vs Docker) & Ensure Port ${PORT} is Active
# ------------------------------------------------------------------------------
DETECTED_BACKEND="local"
if [ "$MODE" = "docker" ]; then
    DETECTED_BACKEND="docker"
elif [ "$MODE" = "local" ]; then
    DETECTED_BACKEND="local"
else
    if is_docker_app_running; then
        DETECTED_BACKEND="docker"
    else
        DETECTED_BACKEND="local"
    fi
fi

if is_server_listening; then
    if is_docker_app_running; then
        echo -e "${COLOR_SUCCESS}✅ Smart Detect: Server Docker terdeteksi aktif di ${TARGET_URL}${COLOR_RESET}"
        DETECTED_BACKEND="docker"
    else
        echo -e "${COLOR_SUCCESS}✅ Smart Detect: Server Lokal terdeteksi aktif di ${TARGET_URL}${COLOR_RESET}"
        DETECTED_BACKEND="local"
    fi
else
    echo -e "${COLOR_WARNING}⚡ Server belum berjalan di port ${PORT}. Menjalankan server otomatis (mode: ${DETECTED_BACKEND})...${COLOR_RESET}"
    if [ "$DETECTED_BACKEND" = "docker" ] && is_docker_daemon_running; then
        docker compose up -d app
        STARTED_BG_SERVER=2
    elif command -v php >/dev/null 2>&1 && [ -f vendor/autoload.php ]; then
        DETECTED_BACKEND="local"
        php artisan serve --host=127.0.0.1 --port="${PORT}" >/dev/null 2>&1 &
        BG_SERVER_PID=$!
        STARTED_BG_SERVER=1
    elif is_docker_daemon_running; then
        DETECTED_BACKEND="docker"
        docker compose up -d app
        STARTED_BG_SERVER=2
    else
        echo -e "${COLOR_ERROR}❌ Tidak dapat memulai server (PHP lokal maupun Docker daemon tidak aktif).${COLOR_RESET}"
        echo -e "   Jalankan ${COLOR_BOLD}make run${COLOR_RESET} atau ${COLOR_BOLD}make dev${COLOR_RESET} terlebih dahulu."
        exit 1
    fi

    echo -n -e "${COLOR_INFO}⏳ Menunggu server siap di ${TARGET_URL}${COLOR_RESET}"
    READY=0
    for _ in $(seq 1 25); do
        if is_server_listening; then
            READY=1
            break
        fi
        echo -n "."
        sleep 1
    done
    echo ""

    if [ "$READY" != "1" ]; then
        echo -e "${COLOR_ERROR}❌ Server belum merespons di ${TARGET_URL}.${COLOR_RESET}"
        exit 1
    fi
    echo -e "${COLOR_SUCCESS}✅ Server aplikasi (${DETECTED_BACKEND}) siap di ${TARGET_URL}!${COLOR_RESET}"
fi

# ------------------------------------------------------------------------------
# 3. Smart Resolve Cloudflared Runner (Local Binary -> Cached .bin -> Docker -> Auto-Download)
# ------------------------------------------------------------------------------
LOCAL_CF_BIN=""
if command -v cloudflared >/dev/null 2>&1; then
    LOCAL_CF_BIN="$(command -v cloudflared)"
elif [ -x "./.bin/cloudflared.exe" ]; then
    LOCAL_CF_BIN="./.bin/cloudflared.exe"
elif [ -x "./.bin/cloudflared" ]; then
    LOCAL_CF_BIN="./.bin/cloudflared"
fi

TUNNEL_CMD=()
if [ "$MODE" = "docker" ] && is_docker_daemon_running; then
    echo -e "${COLOR_INFO}🐳 Mode Docker dipilih: Menggunakan container '${CF_CONTAINER_NAME}' (cloudflare/cloudflared:latest)...${COLOR_RESET}"
    docker rm -f "$CF_CONTAINER_NAME" >/dev/null 2>&1 || true
    TUNNEL_CMD=(docker run --rm --name "$CF_CONTAINER_NAME" --add-host=host.docker.internal:host-gateway cloudflare/cloudflared:latest tunnel --no-autoupdate --protocol http2 --url "http://host.docker.internal:${PORT}")
elif [ -n "$LOCAL_CF_BIN" ]; then
    echo -e "${COLOR_INFO}🔒 Menggunakan binary cloudflared lokal (${LOCAL_CF_BIN}) [Mode: ${DETECTED_BACKEND}]...${COLOR_RESET}"
    TUNNEL_CMD=("$LOCAL_CF_BIN" tunnel --no-autoupdate --protocol http2 --url "${TARGET_URL}")
elif is_docker_daemon_running; then
    echo -e "${COLOR_INFO}🐳 Binary cloudflared lokal tidak ditemukan, menggunakan container Docker '${CF_CONTAINER_NAME}'...${COLOR_RESET}"
    docker rm -f "$CF_CONTAINER_NAME" >/dev/null 2>&1 || true
    TUNNEL_CMD=(docker run --rm --name "$CF_CONTAINER_NAME" --add-host=host.docker.internal:host-gateway cloudflare/cloudflared:latest tunnel --no-autoupdate --protocol http2 --url "http://host.docker.internal:${PORT}")
else
    # Auto-download portable cloudflared binary into ./.bin so local mode works zero-config without Docker
    mkdir -p ./.bin
    OS_NAME="$(uname -s 2>/dev/null || echo Unknown)"
    if [[ "$OS_NAME" =~ MINGW|MSYS|CYGWIN ]] || [ "${OS:-}" = "Windows_NT" ]; then
        echo -e "${COLOR_INFO}⬇️  Mengunduh portable cloudflared.exe untuk Windows ke ./.bin/cloudflared.exe...${COLOR_RESET}"
        curl -fL "https://github.com/cloudflare/cloudflared/releases/latest/download/cloudflared-windows-amd64.exe" -o "./.bin/cloudflared.exe"
        chmod +x "./.bin/cloudflared.exe" 2>/dev/null || true
        LOCAL_CF_BIN="./.bin/cloudflared.exe"
    elif [ "$OS_NAME" = "Linux" ]; then
        echo -e "${COLOR_INFO}⬇️  Mengunduh portable cloudflared untuk Linux ke ./.bin/cloudflared...${COLOR_RESET}"
        curl -fL "https://github.com/cloudflare/cloudflared/releases/latest/download/cloudflared-linux-amd64" -o "./.bin/cloudflared"
        chmod +x "./.bin/cloudflared"
        LOCAL_CF_BIN="./.bin/cloudflared"
    fi

    if [ -n "$LOCAL_CF_BIN" ] && [ -f "$LOCAL_CF_BIN" ]; then
        echo -e "${COLOR_SUCCESS}✅ Binary cloudflared siap di ${LOCAL_CF_BIN}${COLOR_RESET}"
        TUNNEL_CMD=("$LOCAL_CF_BIN" tunnel --no-autoupdate --protocol http2 --url "${TARGET_URL}")
    else
        echo -e "${COLOR_ERROR}❌ Gagal menemukan atau mengunduh cloudflared.${COLOR_RESET}"
        exit 1
    fi
fi

echo -e "${COLOR_INFO}🚀 Membuka jalur HTTPS publik (*.trycloudflare.com)...${COLOR_RESET}"
echo -e "${COLOR_WARNING}👉 Tekan Ctrl+C (atau Cmd+C) kapan saja untuk mematikan tunnel & melepas domain.${COLOR_RESET}"
echo ""

mkdir -p storage/logs
TUNNEL_LOG_FILE="storage/logs/cloudflared-tunnel-${PORT}.log"
: > "$TUNNEL_LOG_FILE"

# Start cloudflared in background so trap SIGINT/SIGTERM immediately catches Ctrl+C and kills TUNNEL_PID
"${TUNNEL_CMD[@]}" >"$TUNNEL_LOG_FILE" 2>&1 &
TUNNEL_PID=$!

ANNOUNCED_URL=0
while kill -0 "$TUNNEL_PID" 2>/dev/null; do
    if [ "$ANNOUNCED_URL" = "0" ] && [ -f "$TUNNEL_LOG_FILE" ]; then
        MATCHED_URL="$(grep -Eo 'https://[a-zA-Z0-9-]+\.trycloudflare\.com' "$TUNNEL_LOG_FILE" | head -n 1 || true)"
        if [ -n "$MATCHED_URL" ]; then
            ANNOUNCED_URL=1
            echo ""
            echo -e "${COLOR_SUCCESS}${COLOR_BOLD}╔══════════════════════════════════════════════════════════════════════╗${COLOR_RESET}"
            echo -e "${COLOR_SUCCESS}${COLOR_BOLD}║ 🌍 CLOUDFLARE FREE HTTPS TUNNEL AKTIF (${DETECTED_BACKEND^^} MODE)                  ║${COLOR_RESET}"
            echo -e "${COLOR_SUCCESS}${COLOR_BOLD}╠══════════════════════════════════════════════════════════════════════╣${COLOR_RESET}"
            echo -e "${COLOR_SUCCESS}${COLOR_BOLD}║ 🔗 URL Publik HTTPS : ${MATCHED_URL}${COLOR_RESET}"
            echo -e "${COLOR_SUCCESS}${COLOR_BOLD}║ 📱 Scan Barcode HP  : ${MATCHED_URL}/scan-barcode${COLOR_RESET}"
            echo -e "${COLOR_SUCCESS}${COLOR_BOLD}║ 🔐 Login SuperAdmin : admin@web.inventaris  (Pass: password)        ║${COLOR_RESET}"
            echo -e "${COLOR_SUCCESS}${COLOR_BOLD}║ 🛡️  Proxy & PWA      : HTTPS ForceScheme + ServiceWorker Aktif       ║${COLOR_RESET}"
            echo -e "${COLOR_SUCCESS}${COLOR_BOLD}╚══════════════════════════════════════════════════════════════════════╝${COLOR_RESET}"
            echo -e "${COLOR_WARNING}👉 Tekan Ctrl+C / Cmd+C untuk menghentikan tunnel & melepaskan domain Cloudflare.${COLOR_RESET}"
            echo ""
        fi
    fi
    sleep 1
done

wait "$TUNNEL_PID" 2>/dev/null || true
