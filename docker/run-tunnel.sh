#!/usr/bin/env bash
# ==============================================================================
# Cloudflare Free HTTPS Quick Tunnel Runner — Inventaris Diskominfo Kab. Garut
# Exposes local/Docker Laravel server (port 8000) to a public HTTPS URL
# (*.trycloudflare.com) with full CSRF, Session, Asset, and CRUD compatibility.
# ==============================================================================

set -u

COLOR_RESET="\033[0m"
COLOR_INFO="\033[36m"
COLOR_SUCCESS="\033[32m"
COLOR_WARNING="\033[33m"
COLOR_ERROR="\033[31m"
COLOR_BOLD="\033[1m"

PORT="${PORT:-8000}"
TARGET_URL="http://127.0.0.1:${PORT}"
STARTED_BG_SERVER=0
BG_SERVER_PID=""

echo -e "${COLOR_INFO}${COLOR_BOLD}======================================================================${COLOR_RESET}"
echo -e "${COLOR_INFO}${COLOR_BOLD} 🌐 Cloudflare Free HTTPS Tunnel — Inventaris Diskominfo Kab. Garut${COLOR_RESET}"
echo -e "${COLOR_INFO}${COLOR_BOLD}======================================================================${COLOR_RESET}"

# ------------------------------------------------------------------------------
# 1. Ensure Production Frontend Assets Exist for Remote HTTPS Clients
# ------------------------------------------------------------------------------
if [ ! -f public/build/manifest.json ]; then
    echo -e "${COLOR_INFO}🎨 Memastikan aset produksi (public/build) siap untuk akses HTTPS jarak jauh...${COLOR_RESET}"
    if command -v bun >/dev/null 2>&1 && [ -d node_modules ]; then
        bun run build
    elif command -v npm >/dev/null 2>&1 && [ -d node_modules ]; then
        npm run build
    elif command -v docker >/dev/null 2>&1; then
        docker compose run --rm app bun run build
    fi
fi

# ------------------------------------------------------------------------------
# 2. Check if Laravel Server is Listening on Port ${PORT}; Auto-Start if Needed
# ------------------------------------------------------------------------------
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

if is_server_listening; then
    echo -e "${COLOR_SUCCESS}✅ Server aplikasi terdeteksi aktif di ${TARGET_URL}${COLOR_RESET}"
else
    echo -e "${COLOR_WARNING}⚡ Server belum berjalan di port ${PORT}. Menjalankan server secara otomatis...${COLOR_RESET}"
    if command -v php >/dev/null 2>&1 && [ -f vendor/autoload.php ]; then
        php artisan serve --host=127.0.0.1 --port="${PORT}" >/dev/null 2>&1 &
        BG_SERVER_PID=$!
        STARTED_BG_SERVER=1
    elif command -v docker >/dev/null 2>&1; then
        docker compose up -d app
        STARTED_BG_SERVER=2
    else
        echo -e "${COLOR_ERROR}❌ Tidak dapat memulai server lokal (PHP atau Docker tidak ditemukan).${COLOR_RESET}"
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
        echo -e "   Pastikan ${COLOR_BOLD}make run${COLOR_RESET} atau ${COLOR_BOLD}make dev${COLOR_RESET} berjalan tanpa error."
        exit 1
    fi
    echo -e "${COLOR_SUCCESS}✅ Server aplikasi siap di ${TARGET_URL}!${COLOR_RESET}"
fi

cleanup() {
    echo ""
    echo -e "${COLOR_WARNING}🛑 Menutup Cloudflare HTTPS Tunnel...${COLOR_RESET}"
    if [ "$STARTED_BG_SERVER" = "1" ] && [ -n "$BG_SERVER_PID" ]; then
        kill "$BG_SERVER_PID" 2>/dev/null || true
    fi
    echo -e "${COLOR_SUCCESS}✅ Tunnel dihentikan.${COLOR_RESET}"
    exit 0
}
trap cleanup SIGINT SIGTERM EXIT

# ------------------------------------------------------------------------------
# 3. Determine Cloudflared Runner (Host Binary vs Docker Fallback)
# ------------------------------------------------------------------------------
TUNNEL_CMD=()
if command -v cloudflared >/dev/null 2>&1; then
    echo -e "${COLOR_INFO}🔒 Menggunakan binary 'cloudflared' lokal (protokol HTTP/2)...${COLOR_RESET}"
    TUNNEL_CMD=(cloudflared tunnel --no-autoupdate --protocol http2 --url "${TARGET_URL}")
elif command -v docker >/dev/null 2>&1; then
    echo -e "${COLOR_INFO}🐳 Binary 'cloudflared' lokal tidak ditemukan, menggunakan image Docker 'cloudflare/cloudflared:latest'...${COLOR_RESET}"
    TUNNEL_CMD=(docker run --rm --add-host=host.docker.internal:host-gateway cloudflare/cloudflared:latest tunnel --no-autoupdate --protocol http2 --url "http://host.docker.internal:${PORT}")
else
    echo ""
    echo -e "${COLOR_ERROR}${COLOR_BOLD}╔══════════════════════════════════════════════════════════════════════╗${COLOR_RESET}"
    echo -e "${COLOR_ERROR}${COLOR_BOLD}║ ❌ SMART DIAGNOSTIC: 'cloudflared' Belum Terinstal                   ║${COLOR_RESET}"
    echo -e "${COLOR_ERROR}${COLOR_BOLD}╚══════════════════════════════════════════════════════════════════════╝${COLOR_RESET}"
    echo -e "  Untuk membuka tunnel HTTPS gratis Cloudflare tanpa Docker, install ${COLOR_BOLD}cloudflared${COLOR_RESET}:"
    echo -e "  • macOS (Homebrew) : ${COLOR_BOLD}brew install cloudflared${COLOR_RESET}"
    echo -e "  • Ubuntu / Debian  : ${COLOR_BOLD}curl -L https://github.com/cloudflare/cloudflared/releases/latest/download/cloudflared-linux-amd64.deb -o cloudflared.deb && sudo dpkg -i cloudflared.deb${COLOR_RESET}"
    echo -e "  • Windows (Winget) : ${COLOR_BOLD}winget install --id Cloudflare.cloudflared${COLOR_RESET}"
    echo ""
    exit 1
fi

echo -e "${COLOR_INFO}🚀 Membuka jalur HTTPS publik (*.trycloudflare.com)...${COLOR_RESET}"
echo ""

# Run cloudflared and highlight the generated trycloudflare.com URL
"${TUNNEL_CMD[@]}" 2>&1 | while IFS= read -r line; do
    if [[ "$line" =~ (https://[a-zA-Z0-9-]+\.trycloudflare\.com) ]]; then
        PUBLIC_HTTPS_URL="${BASH_REMATCH[1]}"
        echo ""
        echo -e "${COLOR_SUCCESS}${COLOR_BOLD}╔══════════════════════════════════════════════════════════════════════╗${COLOR_RESET}"
        echo -e "${COLOR_SUCCESS}${COLOR_BOLD}║ 🌍 CLOUDFLARE FREE HTTPS TUNNEL AKTIF & SIAP TEST CRUD!              ║${COLOR_RESET}"
        echo -e "${COLOR_SUCCESS}${COLOR_BOLD}╠══════════════════════════════════════════════════════════════════════╣${COLOR_RESET}"
        echo -e "${COLOR_SUCCESS}${COLOR_BOLD}║ 🔗 URL Publik HTTPS : ${PUBLIC_HTTPS_URL}${COLOR_RESET}"
        echo -e "${COLOR_SUCCESS}${COLOR_BOLD}║ 📱 Scan Barcode HP  : ${PUBLIC_HTTPS_URL}/scan-barcode${COLOR_RESET}"
        echo -e "${COLOR_SUCCESS}${COLOR_BOLD}║ 🔐 Login SuperAdmin : admin@web.inventaris  (Pass: password)        ║${COLOR_RESET}"
        echo -e "${COLOR_SUCCESS}${COLOR_BOLD}║ 🛡️  Proxy & CRUD     : HTTPS ForceScheme + TrustProxies Aktif        ║${COLOR_RESET}"
        echo -e "${COLOR_SUCCESS}${COLOR_BOLD}╚══════════════════════════════════════════════════════════════════════╝${COLOR_RESET}"
        echo -e "${COLOR_WARNING}👉 Tekan Ctrl+C kapan saja untuk menutup akses publik ini.${COLOR_RESET}"
        echo ""
    else
        echo "$line"
    fi
done
