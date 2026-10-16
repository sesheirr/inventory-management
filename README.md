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

## Quick Setup Guide

Proyek ini menggunakan **Bun** secara eksklusif untuk manajemen paket dan *bundler* frontend. Anda dapat menjalankan aplikasi ini dengan **Docker (`make dev`)** maupun **Tanpa Docker (`make run`)**.

### Opsi 1: Menggunakan Docker (Direkomendasikan)

Pastikan Docker Desktop sudah menyala, lalu jalankan satu perintah berikut:

```bash
make dev
```

Perintah `make dev` akan secara otomatis:
- Menyiapkan container PHP 8.4, MySQL 8.0, dan Bun + Vite HMR.
- Membuat file `.env` dan `APP_KEY` jika belum tersedia.
- Menjalankan **Smart Database Sync (`php artisan inventory:smart-db-sync`)**.
- Menjalankan server di:
  - **Aplikasi Web**: `http://localhost:8000`
  - **Vite HMR (Bun)**: `http://localhost:5173`
  - **Laravel Telescope (Admin)**: `http://localhost:8000/telescope`

### Opsi 2: Tanpa Docker (Smart Local Runner)

Jika ingin menjalankan langsung menggunakan PHP, Composer, MySQL/SQLite, dan **Bun** di mesin lokal:

```bash
make run
```

---

## Smart Database Detection (`inventory:smart-db-sync`)

Setiap kali Anda menjalankan `make dev` atau `make run`, perintah `php artisan inventory:smart-db-sync` akan memeriksa kondisi database lokal secara pintar:

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

## Perintah Utilitas & Pengujian (`Makefile`)

```bash
# Menjalankan aplikasi dengan Docker + Smart DB Sync
make dev

# Menjalankan aplikasi tanpa Docker (Local PHP + Bun)
make run

# Menjalankan seluruh pengujian (PHPUnit Feature & Unit Tests)
make test

# Memeriksa kualitas kode & pemformatan (Laravel Pint + TypeScript + Bun Vite Build)
make lint

# Menjalankan perintah Bun di dalam container Docker
make bun c="run build"
```
