# Panduan Deployment Produksi — SIM-Inventaris Diskominfo Kabupaten Garut (`v2`)

> **Lead Architect & Principal Contributor:** [fwzann-abd](https://github.com/fwzann-abd)  
> **Co-Collaborator (System Design, Architecture, Performance, UI/UX & Mobile-First Engineering):** [msayib](https://github.com/msayib)

Dokumen ini menjelaskan langkah-langkah lengkap untuk men-deploy **SIM-Inventaris Diskominfo Kabupaten Garut (`v2`)** ke server produksi (VPS Linux Ubuntu/Debian maupun Docker Container), termasuk konfigurasi **Webhook & Realtime Queue Server**, **HTTPS / Deep Link PWA**, dan otomatisasi menggunakan `make deploy` (`scripts/deploy.sh`).

---

## 1. Spesifikasi Kebutuhan Server

| Komponen | Versi yang Direkomendasikan | Catatan |
| :--- | :--- | :--- |
| **OS** | Ubuntu 22.04 / 24.04 LTS atau Debian 12 | Mendukung Docker maupun Native Bare-Metal |
| **PHP** | `^8.3` | Ekstensi: `pdo_mysql`, `pdo_sqlite`, `gd`, `zip`, `intl`, `bcmath`, `pcntl`, `exif` |
| **JavaScript Runtime** | **Bun `>= 1.1`** | **Wajib menggunakan `bun` (bukan `npm`)** |
| **Database** | MySQL `8.4` / MariaDB `10.11+` / SQLite `3.40+` | MySQL direkomendasikan untuk produksi |
| **Web Server** | Nginx / Caddy (dengan HTTPS TLS) | HTTPS wajib agar Kamera Barcode, Geolocation, dan PWA Home Screen aktif |
| **Process Manager** | Supervisor / systemd / Docker Compose | Menjaga `inventory:webhook-server` tetap berjalan 24/7 |

---

## 2. Struktur Branch Resmi Repository

Repository ini hanya memelihara **3 branch utama yang bersih**:
- `main` — Rilis stabil utama (Baseline v1).
- `dev` — Staging & integrasi pengembangan.
- `v2` — Arsitektur terbaru **SIM-Inventaris v2** (Hybrid Inertia React 19 + Blade Warm Dark Mode + Realtime Webhook + Observability 1000ms + Mobile Native UX + PWA Deep Link).

---

## 3. Cara Tercepat Deploy (Otomatis 1 Perintah)

Kami telah menyediakan script otomatis di [`scripts/deploy.sh`](../scripts/deploy.sh) yang dapat dijalankan langsung melalui `Makefile`:

```bash
# 1. Clone repository & checkout branch v2
git clone <URL_REPOSITORY_ANDA> /var/www/sim-inventaris
cd /var/www/sim-inventaris
git checkout v2

# 2. Salin & sesuaikan konfigurasi .env produksi
cp .env.example .env
nano .env

# 3. Jalankan script deployment otomatis
make deploy
# atau langsung:
./scripts/deploy.sh --branch=v2
```

### Opsi Tambahan pada `scripts/deploy.sh`
- `./scripts/deploy.sh --docker` — Menjalankan seluruh tahapan deploy di dalam container Docker (`docker compose`).
- `./scripts/deploy.sh --native` — Menjalankan deploy langsung di host server (PHP + Composer + Bun).
- `./scripts/deploy.sh --skip-git` — Menjalankan build, migrasi, dan optimasi cache tanpa melakukan `git pull`.
- `./scripts/deploy.sh --dry-run` — Simulasi langkah deployment tanpa mengeksekusi perubahan.

---

## 4. Konfigurasi `.env` Produksi

Pastikan nilai berikut diatur dengan benar pada file `.env` di server produksi:

```dotenv
APP_NAME="Inventaris Diskominfo Garut"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://inventaris.garutkab.go.id

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=inventory
DB_USERNAME=inventory
DB_PASSWORD=password_kuat_anda

SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database

# Cloudinary / Local Media Storage
FILESYSTEM_DISK=cloudinary
```

---

## 5. Menjalankan Webhook Server & Realtime Queue di Produksi (Supervisor)

Saat pengembangan lokal, perintah `make dev` atau `make run` **otomatis menyalakan**:
1. **Laravel HTTP Server** (`:8000`)
2. **Bun Vite HMR Server** (`:5173`)
3. **Webhook Broker & Realtime Queue Server** (`php artisan inventory:webhook-server --host=0.0.0.0 --port=8085`)

Di server produksi (VPS tanpa Docker), gunakan **Supervisor** (`/etc/supervisor/conf.d/sim-inventaris-webhook.conf`) agar Webhook Server & Queue Worker selalu berjalan otomatis di latar belakang:

```ini
[program:sim-inventaris-webhook]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/sim-inventaris/artisan inventory:webhook-server --host=127.0.0.1 --port=8085
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=1
redirect_stderr=true
stdout_logfile=/var/www/sim-inventaris/storage/logs/webhook-worker.log
stopwaitsecs=3600
```

Aktifkan konfigurasi Supervisor:
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start sim-inventaris-webhook:*
```

---

## 6. Konfigurasi Nginx + HTTPS + Deep Link PWA (`.well-known`)

Agar fitur **1-Click Install ke Home Screen (PWA)**, **Fullscreen Camera Barcode Scanner**, **Geolocation**, dan **Deep Link Otomatis (`/s/*`)** berjalan sempurna di Android & iOS/iPadOS, pastikan domain menggunakan **HTTPS** dan melayani direktori `/.well-known/`:

```nginx
server {
    listen 80;
    server_name inventaris.garutkab.go.id;
    return 301 https://$host$request_uri;
}

server {
    listen 443 ssl http2;
    server_name inventaris.garutkab.go.id;
    root /var/www/sim-inventaris/public;

    index index.php;
    charset utf-8;
    client_max_body_size 25M;

    # Pastikan file manifest PWA & Deep Link (.well-known) memiliki header JSON yang tepat
    location ~ ^/(\.well-known/(assetlinks\.json|apple-app-site-association)|manifest\.(webmanifest|json))$ {
        default_type application/json;
        add_header Cache-Control "public, max-age=3600";
        try_files $uri =404;
    }

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

---

## 7. Checklist Verifikasi Pasca-Deployment

Setelah `make deploy` selesai dijalankan:
1. Buka `https://domain-anda.go.id` dari perangkat **Mobile / Tablet** → pastikan popup **"Install ke Home Screen"** muncul dengan tombol **Install 1-Klik** serta tombol **Aktifkan Semua Izin (Kamera, Lokasi & Deep Link)**.
2. Login sebagai Admin/SuperAdmin → buka menu **Observability (`/observability`)** dan pastikan indikator **Live 1000ms Heartbeat** berdenyut setiap 1 detik serta pagination trace berfungsi.
3. Buka menu **Log Aktivitas (`/activity-logs`)** → pastikan seluruh aktivitas (`LOGIN`, `CREATE`, `UPDATE`, `DELETE`, `APPROVE`, `REJECT`, `SCAN`, `EXPORT`, `WEBHOOK`) tercatat lengkap beserta filter & sorting.
4. Buka menu **Webhook (`/webhooks`)** → klik **Ping** pada endpoint dan pastikan status `delivered` tercatat secara realtime di lonceng notifikasi tanpa perlu me-refresh halaman.
