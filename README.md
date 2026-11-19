# SIM-Inventaris TIK — Diskominfo Kabupaten Garut

Sistem Informasi Manajemen Inventaris, Pemetaan Ruangan, Pemindaian Barcode, dan Pelacakan Mutasi Aset TIK untuk **Dinas Komunikasi dan Informatika (Diskominfo) Kabupaten Garut**.

---

## Fitur Utama

1. **Landing Page Resmi & Portal Aset (`GET /`)**
   - Menampilkan metrik inventaris langsung dari database (jumlah SKU, total stok unit, ruangan & bidang, transaksi mutasi), kartu pratinjau manifest aset NOC, serta pengalih tema (`Terang`, `Gelap`, `Sistem`).
2. **Autentikasi & Masuk Cepat 1-Klik (`Saved Login Profile`)**
   - Saat memilih **Ingat Saya**, perangkat menyimpan profil terenkripsi selama **7 hari** (auto-clear setelah 1 minggu atau dapat dihapus manual kapan saja).
   - Mendukung pemilih tema collapsible di pojok kanan atas pada tampilan desktop maupun mobile.
3. **Manajemen Barang (Tampilan Tabel & Grid + Pemindaian Barcode)**
   - Beralih antara tampilan **Tabel** atau **Grid** (tersimpan otomatis di preferensi browser).
   - Pemindaian barcode kamera langsung (`/scan-barcode`) & cetak label barcode PDF (`/products/{id}/barcode/print`).
4. **Alur Mutasi & Pengembalian Berjenjang (`Enterprise Shipment Tracking UX`)**
   - Mendukung **Pindah Permanen** dan **Peminjaman Sementara** dengan pelacakan status visual (Pengajuan $\rightarrow$ Persetujuan Admin $\rightarrow$ Pengembalian), indikator SLA jatuh tempo, dan rincian manifest yang dapat dibuka/tutup dengan animasi halus.
5. **Profil Pengguna & Smart Phone Detection + 1:1 Avatar Crop Studio**
   - Deteksi otomatis kode negara & format nomor telepon Indonesia (`08xx`, `628xx`, `+628xx`) maupun internasional (`+60`, `+65`, `+1`, `+81`, dll.) dengan validasi panjang digit yang presisi.
   - Pratinjau foto profil 1:1 secara instan disertai studio pemotongan rasio 1:1 (maksimal 8 MB).
6. **Observability Resmi Laravel Telescope (`/telescope`), Panel Telemetri (`/observability`), & Webhook Studio (`/webhooks`) — Khusus Admin**
   - Terintegrasi dengan paket resmi [`laravel/telescope`](https://laravel.com/framework/docs/telescope) (`/telescope`) dan panel telemetri (`/observability`) dengan header W3C `Server-Timing` serta ringkasan latensi rata-rata/P95.
   - Dilengkapi **Outbound Webhook Studio (`/webhooks`)** berstandar HMAC-SHA256 (`X-Diskominfo-Signature-256`) untuk integrasi event real-time (`product.created`, `mutation.approved`, `stock.low`, dll.) dengan log pengiriman dan fitur uji *Ping*.
   - Seluruh akses Observability dan Webhook dibatasi ketat hanya untuk **Admin & Super Admin**.
7. **Multi-File/Image Uploader Setara Filament (`FilePond` + `Spatie MediaLibrary`)**
   - Mendukung unggah banyak foto/dokumen sekaligus dengan *drag-and-drop*, pratinjau *thumbnail* instan, *image crop* rasio 1:1 / 16:9, koreksi orientasi EXIF, dan penyimpanan koleksi terstruktur.
8. **Arsitektur Frontend v2 (`Inertia.js v2` + `React 19` + `TypeScript` + `Zustand v5` + `Vaul` + `Motion`)**
   - Transisi antar-halaman SPA tanpa *full page reload*, *bottom-sheet drawer* mobile bergaya aplikasi native dengan gestur *drag-to-dismiss* (`Vaul`), baris daftar dengan aksi *swipe* 60fps (`Motion`), dan state management terpusat (`Zustand v5`).
9. **Custom Animated Maintenance Mode (`503`)**
   - Tampilan pemeliharaan terjadwal beranimasi (`resources/views/errors/503.blade.php`) yang dapat dipratinjau kapan saja melalui `/maintenance-preview`.

---

## Quick Setup Guide (Cross-Platform: Windows, macOS, Linux)

Proyek ini mendukung penuh **Windows (PowerShell / CMD tanpa perlu `make` atau Git Bash)**, **macOS**, dan **Linux**—baik menggunakan **Docker**, **Bun**, maupun **Node.js (`npm`)**.

### 1. Setup Otomatis (Direkomendasikan untuk PC Baru / Kondisi Beragam)

| Sistem Operasi | Perintah | Keterangan |
| :--- | :--- | :--- |
| **Windows (Tanpa Make / Git Bash)** | `.\onboarding.bat` *(atau klik ganda di Explorer)* | Mendeteksi otomatis Docker, Laragon/Herd/XAMPP/PHP, Composer, Bun, dan Node.js; menyediakan panduan instalasi jika ada yang kurang. |
| **macOS / Linux / Git Bash** | `./setup.sh` | Mendeteksi otomatis Bun/Node.js, PHP, Composer, atau Docker lalu menjalankan setup & server. |
| **Sudah punya Bun / Node.js** | `bun run setup` atau `npm run setup` | Menyiapkan `.env`, `vendor/`, `node_modules/`, database, dan build awal lintas OS. |

### 2. Menjalankan Server Development

- **Tanpa Docker (Local Mode — PHP + Bun/Node + SQLite/MySQL)**:
  - Windows: `.\dev.bat` (atau `bun run start` / `npm run start`)
  - macOS / Linux: `bun run start` / `npm run start` / `make run`
- **Dengan Docker (Docker Mode — PHP 8.3 + MySQL 8.4 + Bun)**:
  - Windows: `.\onboarding.bat --docker` atau `.\dev.bat dev` (atau `bun run start:docker` / `npm run start:docker`)
  - macOS / Linux: `bun run start:docker` / `npm run start:docker` / `make dev`

Setelah server berjalan, aplikasi dapat diakses di:
- **Aplikasi Web**: `http://localhost:8000`
- **LAN HTTPS (Mobile Chrome PWA)**: `https://<IP_LAN_ANDA>:8443`
- **Vite HMR**: `http://localhost:5173`
- **Webhook Broker**: `http://localhost:8085/webhook`
- **Laravel Telescope (Admin)**: `http://localhost:8000/telescope`

---

## Padanan Perintah Cross-Platform (`package.json` / `dev.bat` / `Makefile`)

Pengguna Windows **tidak perlu** menginstal `make` atau Git Bash. Seluruh perintah tersedia lewat `bun run`, `npm run`, maupun `.\dev.bat` (`Makefile` di macOS/Linux tetap berfungsi sebagai alias tipis):

| Tugas | Windows (`dev.bat`) | Bun / Node.js (`package.json`) | macOS / Linux (`Makefile`) |
| :--- | :--- | :--- | :--- |
| Onboarding interaktif | `.\onboarding.bat` | `bun run setup` / `npm run setup` | `./setup.sh` / `make setup` |
| Jalankan Local Mode | `.\dev.bat` | `bun run start` / `npm run start` | `make run` |
| Jalankan Docker Mode | `.\dev.bat dev` | `bun run start:docker` / `npm run start:docker` | `make dev` |
| Cloudflare HTTPS Tunnel | `.\dev.bat tunnel` | `bun run tunnel` / `npm run tunnel` | `make tunnel` |
| Lint + Typecheck + Build | `.\dev.bat lint` | `bun run lint` / `npm run lint` | `make lint` |
| Jalankan Test Suite | `.\dev.bat test` | `bun run test` / `npm run test` | `make test` |
| Cek / Ganti Database | `.\dev.bat db:sqlite` | `bun run db:sqlite` / `db:local` / `db:live` | `make db-status` / `use-db:local` |
| Diagnostik PC / Tool | `.\dev.bat doctor` | `bun run doctor` / `npm run doctor` | `make doctor` |
| Bersihkan Cache | `.\dev.bat clean` | `bun run clean` / `npm run clean` | `make clean` |

---

## Smart Database Detection (`inventory:smart-db-sync`)

Setiap kali Anda menjalankan `bun run start` / `npm run start` / `.\dev.bat` / `make dev`, perintah `php artisan inventory:smart-db-sync` akan memeriksa kondisi database lokal secara pintar:

| Kondisi Database Lokal | Strategi Otomatis yang Dijalankan |
| :--- | :--- |
| **1. Kosong Banget (Belum Termigrasi)** | Otomatis menjalankan `php artisan migrate --force` diikuti `php artisan db:seed --force`. |
| **2. Sudah Termigrasi, Data Masih Kosong (`0` Barang)** | Otomatis menjalankan `php artisan db:seed --force` untuk mengisi katalog Diskominfo. |
| **3. Data Sedikit (`Sparse Data`)** | Menjalankan **Populate Seed + Keep Existing** (`updateOrCreate`), sehingga data milik Anda tetap aman dan dilengkapi dengan data sampel Diskominfo. |
| **4. Data Tidak Relevan / Placeholder Lama** | Menjalankan **Keep Identity + Replace Inventory Data with Seeds**: seluruh akun pengguna (`users`) tetap dipertahankan, sedangkan data barang/mutasi placeholder diganti dengan seed resmi Diskominfo. |

Anda juga dapat memicu strategi tertentu secara manual:

```bash
php artisan inventory:smart-db-sync --strategy=auto
php artisan inventory:smart-db-sync --strategy=populate-keep
php artisan inventory:smart-db-sync --strategy=replace-irrelevant
```

---

## Akun Bawaan (Default Credentials)

| Role | Email | Password | Hak Akses |
| :--- | :--- | :--- | :--- |
| **Super Admin** | `fauzan@example.com` | `password` | Akses penuh + Manajemen User + Telescope, Observability & Webhooks |
| **Admin** | `admin@inventory.local` | `admin123` | Persetujuan Mutasi, Kelola Ruangan/Kategori, Telescope, Observability & Webhooks |
| **Staf / Operator** | `test@example.com` | `password` | Pengajuan Mutasi, Pindai Barcode, Lihat Inventaris |

---

## Perintah Utilitas & Pengujian (Bun / npm / Windows / Makefile)

```bash
# Diagnostik tool & konfigurasi PC
bun run doctor        # atau: npm run doctor  |  .\dev.bat doctor

# Menjalankan aplikasi tanpa Docker (Local PHP + Bun/Node)
bun run start         # atau: npm run start   |  .\dev.bat  |  make run

# Menjalankan aplikasi dengan Docker + Smart DB Sync
bun run start:docker  # atau: npm run start:docker  |  .\dev.bat dev  |  make dev

# Menjalankan seluruh pengujian (PHPUnit Feature & Unit Tests)
bun run test          # atau: npm run test    |  .\dev.bat test  |  make test

# Memeriksa kualitas kode & pemformatan (PHP syntax + Pint + TypeScript + Vite Build)
bun run lint          # atau: npm run lint    |  .\dev.bat lint  |  make lint

# Menjalankan perintah Bun di dalam container Docker (opsional via Makefile)
make bun c="run build"
```
