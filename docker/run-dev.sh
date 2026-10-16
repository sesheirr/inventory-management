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

# Clean up stale Vite hot file if present
rm -f public/hot 2>/dev/null || true

echo "=========================================================="
echo "🚀 Starting Laravel + Bun Vite Development Environment"
echo "👉 Laravel Server: http://localhost:8000"
echo "⚡ Vite HMR:       http://localhost:5173"
echo "⏹️  Press Ctrl+C to stop both servers gracefully."
echo "=========================================================="

# Graceful cleanup on exit
cleanup() {
    echo ""
    echo "🛑 Shutting down dev servers..."
    rm -f public/hot 2>/dev/null || true
    kill "$LARAVEL_PID" "$VITE_PID" 2>/dev/null || true
    wait "$LARAVEL_PID" "$VITE_PID" 2>/dev/null || true
    echo "✅ Dev servers stopped."
    exit 0
}
trap cleanup SIGINT SIGTERM EXIT

# Start Laravel serve in background
php artisan serve --host=0.0.0.0 --port=8000 &
LARAVEL_PID=$!

# Start Bun Vite in background
bun run dev &
VITE_PID=$!

# Wait for both processes
wait -n "$LARAVEL_PID" "$VITE_PID"
