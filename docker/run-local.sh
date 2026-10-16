#!/usr/bin/env bash
# ==============================================================================
# Magic All-in-One Local Runner (Non-Docker) — Inventaris Diskominfo Kab. Garut
# Detects user .env, validates PHP/DB/Node/Bun prerequisites, auto-heals common
# setup gaps, and provides smart actionable diagnostics if anything fails.
# ==============================================================================

set -u

COLOR_RESET="\033[0m"
COLOR_INFO="\033[36m"
COLOR_SUCCESS="\033[32m"
COLOR_WARNING="\033[33m"
COLOR_ERROR="\033[31m"
COLOR_BOLD="\033[1m"

PORT="${PORT:-8000}"
VITE_PORT="${VITE_PORT:-5173}"

print_box_error() {
    local title="$1"
    shift
    echo ""
    echo -e "${COLOR_ERROR}${COLOR_BOLD}╔══════════════════════════════════════════════════════════════════════╗${COLOR_RESET}"
    echo -e "${COLOR_ERROR}${COLOR_BOLD}║ ❌ SMART DIAGNOSTIC: ${title}${COLOR_RESET}"
    echo -e "${COLOR_ERROR}${COLOR_BOLD}╚══════════════════════════════════════════════════════════════════════╝${COLOR_RESET}"
    for line in "$@"; do
        echo -e "  ${line}"
    done
    echo ""
}

read_env_var() {
    local key="$1"
    local default_val="${2:-}"
    if [ ! -f .env ]; then
        echo "$default_val"
        return
    fi
    local raw
    raw=$(grep -E "^[[:space:]]*${key}=" .env | tail -n 1 | cut -d '=' -f2- | sed -e 's/^[[:space:]]*//' -e 's/[[:space:]]*$//')
    # Strip surrounding quotes
    raw="${raw#\"}"
    raw="${raw%\"}"
    raw="${raw#\'}"
    raw="${raw%\'}"
    if [ -z "$raw" ]; then
        echo "$default_val"
    else
        echo "$raw"
    fi
}

echo -e "${COLOR_INFO}${COLOR_BOLD}======================================================================${COLOR_RESET}"
echo -e "${COLOR_INFO}${COLOR_BOLD} 🚀 Inventaris Diskominfo Garut — Smart Local Runner (Non-Docker)${COLOR_RESET}"
echo -e "${COLOR_INFO}${COLOR_BOLD}======================================================================${COLOR_RESET}"

# ------------------------------------------------------------------------------
# 1. Check PHP CLI & Required Extensions
# ------------------------------------------------------------------------------
if ! command -v php >/dev/null 2>&1; then
    print_box_error "PHP CLI Tidak Ditemukan di Sistem Anda" \
        "${COLOR_WARNING}Penyebab:${COLOR_RESET} Perintah 'php' belum terinstal atau belum masuk ke PATH." \
        "" \
        "${COLOR_SUCCESS}Solusi Cepat:${COLOR_RESET}" \
        "  • macOS (Homebrew) : ${COLOR_BOLD}brew install php composer oven-sh/bun/bun${COLOR_RESET}" \
        "  • Ubuntu / Debian  : ${COLOR_BOLD}sudo apt update && sudo apt install -y php php-cli php-mbstring php-xml php-curl php-gd php-zip php-sqlite3 php-mysql composer${COLOR_RESET}" \
        "  • Windows          : Install ${COLOR_BOLD}Laragon${COLOR_RESET} atau ${COLOR_BOLD}Laravel Herd${COLOR_RESET}" \
        "  • Alternatif Docker: Jalankan ${COLOR_BOLD}make dev${COLOR_RESET} (otomatis memakai container Docker)"
    exit 1
fi

PHP_VERSION=$(php -r 'echo PHP_MAJOR_VERSION.".".PHP_MINOR_VERSION;')
PHP_OK=$(php -r 'echo version_compare(PHP_VERSION, "8.2.0", ">=") ? "1" : "0";')
if [ "$PHP_OK" != "1" ]; then
    print_box_error "Versi PHP Terlalu Lama (Terdeteksi: PHP ${PHP_VERSION})" \
        "${COLOR_WARNING}Penyebab:${COLOR_RESET} Aplikasi ini membutuhkan minimal ${COLOR_BOLD}PHP 8.2+${COLOR_RESET}." \
        "" \
        "${COLOR_SUCCESS}Solusi Cepat:${COLOR_RESET}" \
        "  • macOS : ${COLOR_BOLD}brew upgrade php${COLOR_RESET}" \
        "  • Linux : Gunakan PPA ondrej/php (${COLOR_BOLD}sudo apt install php8.3${COLOR_RESET})" \
        "  • Docker: Jalankan ${COLOR_BOLD}make dev${COLOR_RESET}"
    exit 1
fi

MISSING_EXTS=()
for ext in mbstring xml ctype json openssl pdo fileinfo; do
    if ! php -m | grep -i -q "^${ext}$"; then
        MISSING_EXTS+=("$ext")
    fi
done

if [ ${#MISSING_EXTS[@]} -gt 0 ]; then
    PHP_INI_PATH=$(php --ini | grep "Loaded Configuration" | awk -F': ' '{print $2}')
    print_box_error "Ekstensi PHP Wajib Belum Aktif: ${MISSING_EXTS[*]}" \
        "${COLOR_WARNING}File php.ini aktif:${COLOR_RESET} ${PHP_INI_PATH:-Tidak terdeteksi}" \
        "" \
        "${COLOR_SUCCESS}Solusi Cepat:${COLOR_RESET}" \
        "  • Ubuntu/Debian : ${COLOR_BOLD}sudo apt install $(printf 'php-%s ' "${MISSING_EXTS[@]}")${COLOR_RESET}" \
        "  • Windows/XAMPP : Buka ${COLOR_BOLD}${PHP_INI_PATH:-php.ini}${COLOR_RESET} lalu hapus tanda ';' di depan ${COLOR_BOLD}extension=${MISSING_EXTS[0]}${COLOR_RESET}"
    exit 1
fi

# ------------------------------------------------------------------------------
# 2. Detect Frontend Runtime (Strictly Bun Only)
# ------------------------------------------------------------------------------
FRONTEND_RUNNER=""
if command -v bun >/dev/null 2>&1; then
    FRONTEND_RUNNER="bun"
else
    print_box_error "Bun Runtime Tidak Ditemukan" \
        "${COLOR_WARNING}Penyebab:${COLOR_RESET} Proyek v2 ini mewajibkan Bun untuk menjalankan & mem-build aset frontend (React 19 + Inertia v2 + Vite)." \
        "" \
        "${COLOR_SUCCESS}Solusi Cepat:${COLOR_RESET}" \
        "  • Install Bun (macOS/Linux) : ${COLOR_BOLD}curl -fsSL https://bun.sh/install | bash${COLOR_RESET}" \
        "  • Atau via Homebrew (macOS) : ${COLOR_BOLD}brew install oven-sh/bun/bun${COLOR_RESET}"
    exit 1
fi

# ------------------------------------------------------------------------------
# 3. Check & Parse .env Configuration
# ------------------------------------------------------------------------------
if [ ! -f .env ]; then
    echo -e "${COLOR_WARNING}⚠️  File .env belum ada. Membuat .env otomatis dari .env.example...${COLOR_RESET}"
    cp .env.example .env
fi

DB_CONNECTION=$(read_env_var "DB_CONNECTION" "sqlite")
DB_HOST=$(read_env_var "DB_HOST" "127.0.0.1")
DB_PORT=$(read_env_var "DB_PORT" "3306")
DB_DATABASE=$(read_env_var "DB_DATABASE" "inventory")
DB_USERNAME=$(read_env_var "DB_USERNAME" "root")
DB_PASSWORD=$(read_env_var "DB_PASSWORD" "")
APP_ENV=$(read_env_var "APP_ENV" "local")
APP_URL=$(read_env_var "APP_URL" "http://localhost:${PORT}")

# Smart Host Translation: if .env has DB_HOST=db or mysql (from Docker), override to 127.0.0.1 for local run
EFFECTIVE_DB_HOST="$DB_HOST"
if [ "$DB_HOST" = "db" ] || [ "$DB_HOST" = "mysql" ] || [ "$DB_HOST" = "inventory_db" ]; then
    echo -e "${COLOR_WARNING}ℹ️  Terdeteksi DB_HOST='${DB_HOST}' (hostname Docker) di .env.${COLOR_RESET}"
    echo -e "${COLOR_WARNING}   Mengalihkan koneksi lokal sementara ke 127.0.0.1:${DB_PORT} untuk 'make run'.${COLOR_RESET}"
    EFFECTIVE_DB_HOST="127.0.0.1"
    export DB_HOST="127.0.0.1"
fi

echo -e "${COLOR_INFO}📋 Konfigurasi .env Terdeteksi:${COLOR_RESET}"
echo -e "   • PHP Runtime    : ${COLOR_BOLD}PHP ${PHP_VERSION}${COLOR_RESET}"
echo -e "   • Asset Runner   : ${COLOR_BOLD}${FRONTEND_RUNNER}${COLOR_RESET}"
echo -e "   • APP_ENV / URL  : ${COLOR_BOLD}${APP_ENV}${COLOR_RESET} (${APP_URL})"
echo -e "   • DB_CONNECTION  : ${COLOR_BOLD}${DB_CONNECTION}${COLOR_RESET}"
if [ "$DB_CONNECTION" = "sqlite" ]; then
    echo -e "   • SQLite Path    : ${COLOR_BOLD}${DB_DATABASE:-database/database.sqlite}${COLOR_RESET}"
else
    echo -e "   • Database Host  : ${COLOR_BOLD}${EFFECTIVE_DB_HOST}:${DB_PORT}/${DB_DATABASE}${COLOR_RESET} (user: ${DB_USERNAME})"
fi

# ------------------------------------------------------------------------------
# 4. Composer & Node/Bun Dependencies Auto-Install
# ------------------------------------------------------------------------------
mkdir -p storage/framework/{sessions,views,cache} storage/logs bootstrap/cache
chmod -R ugo+rwX storage bootstrap/cache 2>/dev/null || true

if [ ! -f vendor/autoload.php ]; then
    echo -e "${COLOR_INFO}📦 Menginstal dependensi PHP (composer install)...${COLOR_RESET}"
    if ! command -v composer >/dev/null 2>&1; then
        print_box_error "Folder 'vendor/' Belum Ada & Composer Tidak Ditemukan" \
            "${COLOR_SUCCESS}Solusi Cepat:${COLOR_RESET} Install Composer (${COLOR_BOLD}brew install composer${COLOR_RESET} atau ${COLOR_BOLD}https://getcomposer.org${COLOR_RESET}) lalu jalankan ${COLOR_BOLD}make run${COLOR_RESET} kembali."
        exit 1
    fi
    if ! composer install --no-interaction --prefer-dist; then
        print_box_error "Gagal Menjalankan 'composer install'" \
            "${COLOR_WARNING}Penyebab:${COLOR_RESET} Pastikan koneksi internet aktif dan ekstensi PHP (zip/xml/gd) sudah terpasang." \
            "${COLOR_SUCCESS}Coba jalankan manual:${COLOR_RESET} ${COLOR_BOLD}composer install${COLOR_RESET}"
        exit 1
    fi
fi

if [ ! -d node_modules ]; then
    echo -e "${COLOR_INFO}📦 Menginstal dependensi frontend (${FRONTEND_RUNNER} install)...${COLOR_RESET}"
    if ! ${FRONTEND_RUNNER} install; then
        print_box_error "Gagal Menjalankan '${FRONTEND_RUNNER} install'" \
            "${COLOR_SUCCESS}Coba jalankan manual:${COLOR_RESET} ${COLOR_BOLD}${FRONTEND_RUNNER} install${COLOR_RESET}"
        exit 1
    fi
fi

# Ensure APP_KEY exists
APP_KEY_VAL=$(read_env_var "APP_KEY" "")
if [ -z "$APP_KEY_VAL" ]; then
    echo -e "${COLOR_INFO}🔑 Membuat APP_KEY baru di .env...${COLOR_RESET}"
    php artisan key:generate --ansi
fi

# Ensure storage symlink exists
if [ ! -L public/storage ] && [ ! -d public/storage ]; then
    php artisan storage:link --ansi >/dev/null 2>&1 || true
fi

# ------------------------------------------------------------------------------
# 5. Smart Database Connectivity Check & Auto-Provisioning
# ------------------------------------------------------------------------------
if [ "$DB_CONNECTION" = "sqlite" ]; then
    if ! php -m | grep -i -q "^pdo_sqlite$"; then
        print_box_error "Ekstensi PHP 'pdo_sqlite' Belum Aktif" \
            "${COLOR_WARNING}Penyebab:${COLOR_RESET} .env Anda menggunakan DB_CONNECTION=sqlite tetapi ekstensi pdo_sqlite belum aktif." \
            "${COLOR_SUCCESS}Solusi:${COLOR_RESET} Install ${COLOR_BOLD}php-sqlite3${COLOR_RESET} atau ubah ${COLOR_BOLD}DB_CONNECTION=mysql${COLOR_RESET} di file .env."
        exit 1
    fi

    SQLITE_FILE="${DB_DATABASE:-database/database.sqlite}"
    # If DB_DATABASE looks like a MySQL db name (no slash and not ending in .sqlite), use database/database.sqlite
    if [[ "$SQLITE_FILE" != *"/"* ]] && [[ "$SQLITE_FILE" != *".sqlite"* ]]; then
        SQLITE_FILE="database/database.sqlite"
        export DB_DATABASE="$SQLITE_FILE"
    fi

    if [ ! -f "$SQLITE_FILE" ]; then
        echo -e "${COLOR_INFO}🗄️  Membuat file database SQLite: ${SQLITE_FILE}...${COLOR_RESET}"
        mkdir -p "$(dirname "$SQLITE_FILE")"
        touch "$SQLITE_FILE"
    fi
elif [ "$DB_CONNECTION" = "mysql" ] || [ "$DB_CONNECTION" = "mariadb" ]; then
    if ! php -m | grep -i -q "^pdo_mysql$"; then
        print_box_error "Ekstensi PHP 'pdo_mysql' Belum Aktif" \
            "${COLOR_WARNING}Penyebab:${COLOR_RESET} .env Anda menggunakan DB_CONNECTION=${DB_CONNECTION} tetapi ekstensi pdo_mysql belum aktif." \
            "${COLOR_SUCCESS}Solusi 1 (Install MySQL Driver):${COLOR_RESET} ${COLOR_BOLD}sudo apt install php-mysql${COLOR_RESET} / aktifkan di php.ini" \
            "${COLOR_SUCCESS}Solusi 2 (Gunakan SQLite Tanpa Ribet):${COLOR_RESET} Ubah ${COLOR_BOLD}DB_CONNECTION=sqlite${COLOR_RESET} di file .env"
        exit 1
    fi

    DB_CHECK_RESULT=$(php -r '
        $host = getenv("DB_HOST") ?: "'"$EFFECTIVE_DB_HOST"'";
        $port = getenv("DB_PORT") ?: "'"$DB_PORT"'";
        $db   = "'"$DB_DATABASE"'";
        $user = "'"$DB_USERNAME"'";
        $pass = "'"$DB_PASSWORD"'";
        try {
            $pdo = new PDO("mysql:host={$host};port={$port}", $user, $pass, [
                PDO::ATTR_TIMEOUT => 3,
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            ]);
            $safeDb = str_replace("`", "``", $db);
            $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$safeDb}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            echo "OK";
        } catch (PDOException $e) {
            echo "ERR:" . $e->getCode() . ":" . $e->getMessage();
        }
    ' 2>/dev/null)

    if [[ "$DB_CHECK_RESULT" != "OK" ]]; then
        print_box_error "Tidak Dapat Terhubung ke Database MySQL (${EFFECTIVE_DB_HOST}:${DB_PORT})" \
            "${COLOR_WARNING}Detail Error:${COLOR_RESET} ${DB_CHECK_RESULT#ERR:}" \
            "" \
            "${COLOR_SUCCESS}Apa yang perlu Anda lakukan? Pilih salah satu:${COLOR_RESET}" \
            "  1️⃣  ${COLOR_BOLD}Nyalakan service MySQL lokal Anda:${COLOR_RESET}" \
            "      • macOS (Homebrew) : ${COLOR_BOLD}brew services start mysql${COLOR_RESET}" \
            "      • Linux (systemd)  : ${COLOR_BOLD}sudo systemctl start mysql${COLOR_RESET}" \
            "      • Atau nyalakan hanya container DB Docker: ${COLOR_BOLD}docker compose up -d db${COLOR_RESET}" \
            "  2️⃣  ${COLOR_BOLD}Periksa kredensial di file .env:${COLOR_RESET}" \
            "      Pastikan ${COLOR_BOLD}DB_HOST=${EFFECTIVE_DB_HOST}${COLOR_RESET}, ${COLOR_BOLD}DB_PORT=${DB_PORT}${COLOR_RESET}, ${COLOR_BOLD}DB_USERNAME=${DB_USERNAME}${COLOR_RESET}, dan ${COLOR_BOLD}DB_PASSWORD${COLOR_RESET} sesuai." \
            "  3️⃣  ${COLOR_BOLD}Atau beralih ke SQLite (Zero-Config):${COLOR_RESET}" \
            "      Ubah ${COLOR_BOLD}DB_CONNECTION=sqlite${COLOR_RESET} di file ${COLOR_BOLD}.env${COLOR_RESET} lalu jalankan ${COLOR_BOLD}make run${COLOR_RESET} lagi."
        exit 1
    fi
fi

# ------------------------------------------------------------------------------
# 6. Run Smart Database Sync (Migrate, Auto-Seed, Sparse Populate, or Keep Identity + Replace Irrelevant)
# ------------------------------------------------------------------------------
echo -e "${COLOR_INFO}🔄 Memeriksa migrasi & kondisi data lokal (Smart DB Sync)...${COLOR_RESET}"
if ! php artisan inventory:smart-db-sync --ansi; then
    print_box_error "Sinkronisasi Database Gagal" \
        "${COLOR_WARNING}Penyebab:${COLOR_RESET} Terjadi kendala saat menjalankan 'php artisan inventory:smart-db-sync'." \
        "${COLOR_SUCCESS}Solusi:${COLOR_RESET} Cek konfigurasi database di ${COLOR_BOLD}.env${COLOR_RESET} atau jalankan ${COLOR_BOLD}php artisan migrate:fresh --seed${COLOR_RESET} jika ingin reset bersih."
    exit 1
fi

# ------------------------------------------------------------------------------
# 7. Ensure Production Build Exists (So Cloudflare Tunnel / Remote Devices Work!)
# ------------------------------------------------------------------------------
if [ ! -f public/build/manifest.json ]; then
    echo -e "${COLOR_INFO}🎨 Membangun aset produksi awal (${FRONTEND_RUNNER} run build)...${COLOR_RESET}"
    ${FRONTEND_RUNNER} run build
fi

rm -f public/hot 2>/dev/null || true

echo ""
echo -e "${COLOR_SUCCESS}${COLOR_BOLD}======================================================================${COLOR_RESET}"
echo -e "${COLOR_SUCCESS}${COLOR_BOLD} ✅ Semua Siap! Menjalankan Server Lokal (Tanpa Docker)${COLOR_RESET}"
echo -e "${COLOR_SUCCESS}${COLOR_BOLD} 👉 Aplikasi Web : http://127.0.0.1:${PORT}${COLOR_RESET}"
echo -e "${COLOR_INFO}${COLOR_BOLD} ⚡ Vite HMR     : http://127.0.0.1:${VITE_PORT}${COLOR_RESET}"
echo -e "${COLOR_WARNING}${COLOR_BOLD} 🌐 Ingin akses HTTPS jarak jauh? Buka terminal baru & ketik: make tunnel${COLOR_RESET}"
echo -e "${COLOR_INFO} ⏹️  Tekan Ctrl+C untuk menghentikan semua server.${COLOR_RESET}"
echo -e "${COLOR_SUCCESS}${COLOR_BOLD}======================================================================${COLOR_RESET}"
echo ""

cleanup() {
    echo ""
    echo -e "${COLOR_WARNING}🛑 Menghentikan server lokal...${COLOR_RESET}"
    rm -f public/hot 2>/dev/null || true
    kill "${LARAVEL_PID:-}" "${VITE_PID:-}" 2>/dev/null || true
    wait "${LARAVEL_PID:-}" "${VITE_PID:-}" 2>/dev/null || true
    echo -e "${COLOR_SUCCESS}✅ Server lokal berhenti dengan bersih.${COLOR_RESET}"
    exit 0
}
trap cleanup SIGINT SIGTERM EXIT

php artisan serve --host=0.0.0.0 --port="${PORT}" &
LARAVEL_PID=$!

${FRONTEND_RUNNER} run dev &
VITE_PID=$!

while kill -0 "$LARAVEL_PID" 2>/dev/null && kill -0 "$VITE_PID" 2>/dev/null; do
    sleep 1
done
