#!/usr/bin/env bash
set -e

# Setup .env if not present
if [ ! -f .env ]; then
    echo "Creating .env from .env.example..."
    cp .env.example .env
fi

# Ensure storage and bootstrap directories exist with proper permissions
mkdir -p storage/framework/{sessions,views,cache} storage/logs bootstrap/cache
chmod -R ugo+rwX storage bootstrap/cache 2>/dev/null || true
chmod 644 bootstrap/cache/.gitignore $(find storage -name ".gitignore" 2>/dev/null) 2>/dev/null || true

# If database is SQLite and file doesn't exist, create it
if grep -q "DB_CONNECTION=sqlite" .env && [ ! -f database/database.sqlite ]; then
    mkdir -p database
    touch database/database.sqlite
    chmod 666 database/database.sqlite
fi

# Generate app key if not set
if ! grep -q "APP_KEY=base64:" .env || [ -z "$(grep 'APP_KEY=base64:' .env | cut -d '=' -f2)" ]; then
    php artisan key:generate --ansi
fi

# Smart Docker DB Host Detection: if running inside Docker and DB_HOST=127.0.0.1 cannot connect,
# automatically try host.docker.internal (host Laragon/MySQL) or Docker's 'db' service container.
if [ -f /.dockerenv ] && grep -q "^DB_CONNECTION=mysql" .env 2>/dev/null; then
    if ! php -r '
        $env = @parse_ini_file(".env");
        $h = getenv("DB_HOST") ?: ($env["DB_HOST"] ?? "127.0.0.1");
        $p = getenv("DB_PORT") ?: ($env["DB_PORT"] ?? "3306");
        $u = getenv("DB_USERNAME") ?: ($env["DB_USERNAME"] ?? "root");
        $pw = getenv("DB_PASSWORD") ?: ($env["DB_PASSWORD"] ?? "");
        try { new PDO("mysql:host=$h;port=$p", $u, $pw, [PDO::ATTR_TIMEOUT => 2]); exit(0); } catch (Throwable $e) { exit(1); }
    ' 2>/dev/null; then
        if php -r '
            $env = @parse_ini_file(".env");
            $p = $env["DB_PORT"] ?? "3306";
            $u = $env["DB_USERNAME"] ?? "root";
            $pw = $env["DB_PASSWORD"] ?? "";
            try { new PDO("mysql:host=host.docker.internal;port=$p", $u, $pw, [PDO::ATTR_TIMEOUT => 2]); exit(0); } catch (Throwable $e) { exit(1); }
        ' 2>/dev/null; then
            echo "🐳 Docker detected host MySQL via host.docker.internal"
            export DB_HOST=host.docker.internal
        elif php -r '
            try { new PDO("mysql:host=db;port=3306;dbname=inventory", "inventory", "secret", [PDO::ATTR_TIMEOUT => 3]); exit(0); } catch (Throwable $e) { exit(1); }
        ' 2>/dev/null; then
            echo "🐳 Docker detected container MySQL service (db:3306)"
            export DB_HOST=db
            export DB_PORT=3306
            export DB_DATABASE=inventory
            export DB_USERNAME=inventory
            export DB_PASSWORD=secret
        fi
    fi
fi

# Smart Database Detection & Auto-Sync (Empty DB -> migrate+seed, 0 items -> seed, sparse -> populate+keep, irrelevant -> keep identity + replace with seeds)
php artisan inventory:smart-db-sync --ansi

# Clean up stale Vite hot file if present
rm -f public/hot 2>/dev/null || true

echo "=================================================================="
echo "🚀 Starting Laravel + Bun Vite + LAN HTTPS + Webhook Server"
echo "👉 Laravel Server : http://localhost:8000"
echo "📱 LAN HTTPS PWA  : https://localhost:8443"
echo "⚡ Vite HMR       : http://localhost:5173"
echo "🔔 Webhook Server : http://localhost:8085/webhook"
echo "⏹️  Press Ctrl+C to stop all servers gracefully."
echo "=================================================================="

# Graceful cleanup on exit
cleanup() {
    echo ""
    echo "🛑 Shutting down dev, LAN HTTPS & webhook servers..."
    rm -f public/hot 2>/dev/null || true
    kill "${LARAVEL_PID:-}" "${VITE_PID:-}" "${WEBHOOK_PID:-}" "${LAN_HTTPS_PID:-}" 2>/dev/null || true
    wait "${LARAVEL_PID:-}" "${VITE_PID:-}" "${WEBHOOK_PID:-}" "${LAN_HTTPS_PID:-}" 2>/dev/null || true
    echo "✅ All dev & webhook servers stopped."
    exit 0
}
trap cleanup SIGINT SIGTERM EXIT

# Start Laravel serve in background
php artisan serve --host=0.0.0.0 --port=8000 &
LARAVEL_PID=$!

# Start Bun Vite in background
bun run dev &
VITE_PID=$!

# Start Webhook Broker & Realtime Queue Server in background
php artisan inventory:webhook-server --host=0.0.0.0 --port=8085 &
WEBHOOK_PID=$!

# Start Local LAN HTTPS Proxy for Mobile Chrome PWA installation
if [ -f scripts/lan-https-proxy.mjs ]; then
    HTTPS_PORT=8443 PORT=8000 bun scripts/lan-https-proxy.mjs &
    LAN_HTTPS_PID=$!
fi

# Wait for primary processes
wait -n "$LARAVEL_PID" "$VITE_PID" "$WEBHOOK_PID"
