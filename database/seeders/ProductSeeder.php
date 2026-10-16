<?php

namespace Database\Seeders;

use App\Models\ActivityLog;
use App\Models\Category;
use App\Models\Mutation;
use App\Models\Product;
use App\Models\Room;
use App\Models\User;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Seed Realistic Diskominfo Kabupaten Garut Rooms
        $roomsData = [
            [
                'name' => 'Ruang Server & NOC (Data Center Lt. 2)',
                'person_in_charge' => 'Hendra Wijaya, S.Kom., M.T.',
            ],
            [
                'name' => 'Ruang Bidang Aplikasi Informatika (Aptika)',
                'person_in_charge' => 'Rina Kartika, S.T.',
            ],
            [
                'name' => 'Ruang Bidang Informasi & Komunikasi Publik (IKP)',
                'person_in_charge' => 'Dedi Supriadi, S.I.Kom.',
            ],
            [
                'name' => 'Ruang Bidang Statistik & Persandian',
                'person_in_charge' => 'Agus Setiawan, S.Si.',
            ],
            [
                'name' => 'Ruang Sekretariat & Umum',
                'person_in_charge' => 'Hj. Siti Nurhasanah, S.Sos.',
            ],
            [
                'name' => 'Garut Command Center (GCC)',
                'person_in_charge' => 'Fajar Ramadhan, S.Kom.',
            ],
            [
                'name' => 'Studio Podcast & Media Center Diskominfo',
                'person_in_charge' => 'Gilang Pratama, S.Sn.',
            ],
            [
                'name' => 'Gudang Aset & Logistik TIK',
                'person_in_charge' => 'Asep Saepuloh',
            ],
        ];

        $rooms = [];
        foreach ($roomsData as $roomItem) {
            $room = Room::updateOrCreate(
                ['name' => $roomItem['name']],
                ['person_in_charge' => $roomItem['person_in_charge']]
            );
            $rooms[$roomItem['name']] = $room;
        }

        // 2. Seed Realistic Categories
        $categoriesData = [
            [
                'name' => 'Perangkat Jaringan & Server',
                'description' => 'Perangkat infrastruktur jaringan, router, switch manageable, access point, rack server, dan modul fiber optik NOC Diskominfo.',
            ],
            [
                'name' => 'Komputer & Laptop Kerja',
                'description' => 'Unit PC desktop, workstation, PC All-in-One, dan laptop operasional pegawai serta tim teknis.',
            ],
            [
                'name' => 'Multimedia & Penyiaran',
                'description' => 'Kamera dokumentasi pimpinan, drone liputan udara, audio mixer podcast, mikrofon, dan perangkat produksi konten IKP.',
            ],
            [
                'name' => 'Perangkat Kantor & Cetak',
                'description' => 'Printer multifungsi, scanner dokumen kecepatan tinggi, proyektor ruang rapat, layar interaktif, dan UPS.',
            ],
            [
                'name' => 'Keamanan Siber & Persandian',
                'description' => 'Perangkat keras firewall, modul keamanan jaringan, kamera CCTV IP, dan peralatan pengamanan informasi daerah.',
            ],
            [
                'name' => 'Furnitur & Sarana Ruang Kerja',
                'description' => 'Meja kerja staf, kursi ergonomis, lemari arsip besi tahan api, dan rak penyimpanan logistik TIK.',
            ],
        ];

        $categories = [];
        foreach ($categoriesData as $catItem) {
            $category = Category::updateOrCreate(
                ['name' => $catItem['name']],
                ['description' => $catItem['description']]
            );
            $categories[$catItem['name']] = $category;
        }

        // Remove old English placeholder products ("Product 1" .. "Product 50") if present
        Product::where('name', 'like', 'Product %')
            ->where('description', 'Premium inventory item crafted for modern store operations.')
            ->delete();

        // 3. Seed Rich, Realistic Diskominfo Inventory Items (Data Barang)
        $productsData = [
            [
                'kode_barang' => 'DKI-GRT-2026-001',
                'barcode' => 'BRG-000001',
                'name' => 'Router Core MikroTik CCR2116-12G-4S+',
                'category' => 'Perangkat Jaringan & Server',
                'subcategory' => 'Core Router',
                'edition' => 'Pengadaan APBD 2025',
                'room' => 'Ruang Server & NOC (Data Center Lt. 2)',
                'stock' => 4,
                'price' => 21500000,
                'status' => 'active',
                'description' => 'Router distribusi utama jaringan intra pemerintah daerah Kabupaten Garut di Ruang NOC Lt. 2.',
            ],
            [
                'kode_barang' => 'DKI-GRT-2026-002',
                'barcode' => 'BRG-000002',
                'name' => 'Switch Manageable Cisco Catalyst C9200L-24P-4G',
                'category' => 'Perangkat Jaringan & Server',
                'subcategory' => 'Switch PoE+',
                'edition' => 'Pengadaan APBD 2025',
                'room' => 'Ruang Server & NOC (Data Center Lt. 2)',
                'stock' => 8,
                'price' => 34800000,
                'status' => 'active',
                'description' => 'Switch distribuzione Layer 3 24-Port PoE+ untuk menopang Access Point dan IP Phone gedung utama.',
            ],
            [
                'kode_barang' => 'DKI-GRT-2026-003',
                'barcode' => 'BRG-000003',
                'name' => 'Server Rack Dell PowerEdge R750xs Xeon Silver',
                'category' => 'Perangkat Jaringan & Server',
                'subcategory' => 'Server Aplikasi',
                'edition' => 'Tahun Anggaran 2024',
                'room' => 'Ruang Server & NOC (Data Center Lt. 2)',
                'stock' => 6,
                'price' => 118500000,
                'status' => 'active',
                'description' => 'Server rack 2U cluster virtualisasi aplikasi layanan publik SPBE Pemerintah Kabupaten Garut.',
            ],
            [
                'kode_barang' => 'DKI-GRT-2026-004',
                'barcode' => 'BRG-000004',
                'name' => 'Access Point Ubiquiti UniFi U6-LR WiFi 6',
                'category' => 'Perangkat Jaringan & Server',
                'subcategory' => 'Wireless Access Point',
                'edition' => 'Pengadaan 2025',
                'room' => 'Gudang Aset & Logistik TIK',
                'stock' => 18,
                'price' => 3450000,
                'status' => 'active',
                'description' => 'Unit Access Point WiFi 6 Long Range untuk titik layanan internet ruang rapat dan area pelayanan publik.',
            ],
            [
                'kode_barang' => 'DKI-GRT-2026-005',
                'barcode' => 'BRG-000005',
                'name' => 'Modul SFP+ Transceiver 10G Single Mode LC',
                'category' => 'Perangkat Jaringan & Server',
                'subcategory' => 'Modul Optik',
                'edition' => 'Batch 2026',
                'room' => 'Gudang Aset & Logistik TIK',
                'stock' => 25,
                'price' => 650000,
                'status' => 'active',
                'description' => 'Modul transceiver fiber optik 10Gbps cadangan interkoneksi antar SKPD Kabupaten Garut.',
            ],
            [
                'kode_barang' => 'DKI-GRT-2026-006',
                'barcode' => 'BRG-000006',
                'name' => 'Laptop ASUS ExpertBook B5 Flip OLED Core i7',
                'category' => 'Komputer & Laptop Kerja',
                'subcategory' => 'Laptop Operasional',
                'edition' => 'RAM 16GB / 1TB SSD',
                'room' => 'Ruang Bidang Aplikasi Informatika (Aptika)',
                'stock' => 12,
                'price' => 19750000,
                'status' => 'active',
                'description' => 'Laptop kerja tim pengembang sistem informasi, integrasi API, dan analisa sistem Bidang Aptika.',
            ],
            [
                'kode_barang' => 'DKI-GRT-2026-007',
                'barcode' => 'BRG-000007',
                'name' => 'Apple MacBook Pro 14 M3 Pro (Workstation Multimedia)',
                'category' => 'Komputer & Laptop Kerja',
                'subcategory' => 'Workstation Editing',
                'edition' => '18GB Unified / 512GB',
                'room' => 'Studio Podcast & Media Center Diskominfo',
                'stock' => 3,
                'price' => 33990000,
                'status' => 'active',
                'description' => 'Unit laptop khusus rendering video publikasi daerah, motion graphics, dan desain komunikasi visual.',
            ],
            [
                'kode_barang' => 'DKI-GRT-2026-008',
                'barcode' => 'BRG-000008',
                'name' => 'PC All-in-One Lenovo IdeaCentre AIO 3i 24 Inch',
                'category' => 'Komputer & Laptop Kerja',
                'subcategory' => 'PC Desktop',
                'edition' => 'Core i5 Gen-13',
                'room' => 'Ruang Sekretariat & Umum',
                'stock' => 14,
                'price' => 11200000,
                'status' => 'active',
                'description' => 'Komputer kerja administrasi persuratan Srikandi, kepegawaian, dan keuangan Sekretariat Diskominfo.',
            ],
            [
                'kode_barang' => 'DKI-GRT-2026-009',
                'barcode' => 'BRG-000009',
                'name' => 'PC Workstation Analitik Data & Geospasial',
                'category' => 'Komputer & Laptop Kerja',
                'subcategory' => 'PC Desktop High-End',
                'edition' => 'RTX 4070 / 32GB RAM',
                'room' => 'Ruang Bidang Statistik & Persandian',
                'stock' => 6,
                'price' => 26400000,
                'status' => 'active',
                'description' => 'Komputer pengolahan Satu Data Indonesia (SDI) tingkat Kabupaten Garut dan visualisasi statistik sektoral.',
            ],
            [
                'kode_barang' => 'DKI-GRT-2026-010',
                'barcode' => 'BRG-000010',
                'name' => 'Kamera Mirrorless Sony Alpha A7 IV Body + FE 24-70mm GM',
                'category' => 'Multimedia & Penyiaran',
                'subcategory' => 'Kamera Liputan',
                'edition' => 'Full Frame 33MP',
                'room' => 'Ruang Bidang Informasi & Komunikasi Publik (IKP)',
                'stock' => 4,
                'price' => 48500000,
                'status' => 'active',
                'description' => 'Kamera utama dokumentasi kegiatan Pemerintah Kabupaten Garut dan siaran pers Humas IKP.',
            ],
            [
                'kode_barang' => 'DKI-GRT-2026-011',
                'barcode' => 'BRG-000011',
                'name' => 'Drone DJI Air 3 Fly More Combo (RC-N2)',
                'category' => 'Multimedia & Penyiaran',
                'subcategory' => 'Drone Dokumentasi',
                'edition' => 'Dual Primary Camera',
                'room' => 'Ruang Bidang Informasi & Komunikasi Publik (IKP)',
                'stock' => 2,
                'price' => 23800000,
                'status' => 'active',
                'description' => 'Drone pemetaan udara dan peliputan infrastruktur daerah serta kesiapsiagaan bencana.',
            ],
            [
                'kode_barang' => 'DKI-GRT-2026-012',
                'barcode' => 'BRG-000012',
                'name' => 'Audio Production Console Rodecaster Pro II + 4 PodMic',
                'category' => 'Multimedia & Penyiaran',
                'subcategory' => 'Perangkat Podcast',
                'edition' => 'Paket Studio Podcast',
                'room' => 'Studio Podcast & Media Center Diskominfo',
                'stock' => 2,
                'price' => 18900000,
                'status' => 'active',
                'description' => 'Konsol mixer audio digital dan mikrofon untuk produksi podcast sosialisasi program Pemkab Garut.',
            ],
            [
                'kode_barang' => 'DKI-GRT-2026-013',
                'barcode' => 'BRG-000013',
                'name' => 'Kamera PTZ Video Conference Logitech Rally Plus',
                'category' => 'Multimedia & Penyiaran',
                'subcategory' => 'Video Conference',
                'edition' => 'Ultra-HD ConferenceCam',
                'room' => 'Garut Command Center (GCC)',
                'stock' => 3,
                'price' => 42000000,
                'status' => 'active',
                'description' => 'Perangkat kamera PTZ dan speakerphone modular untuk rapat koordinasi daring lintas kementerian di GCC.',
            ],
            [
                'kode_barang' => 'DKI-GRT-2026-014',
                'barcode' => 'BRG-000014',
                'name' => 'Smart Interactive Flat Panel 75 Inch 4K Touch',
                'category' => 'Perangkat Kantor & Cetak',
                'subcategory' => 'Layar Interaktif',
                'edition' => 'Android + OPS Windows',
                'room' => 'Garut Command Center (GCC)',
                'stock' => 4,
                'price' => 56000000,
                'status' => 'active',
                'description' => 'Layar sentuh interaktif pemantauan dashboard eksekutif dan paparan pimpinan di Garut Command Center.',
            ],
            [
                'kode_barang' => 'DKI-GRT-2026-015',
                'barcode' => 'BRG-000015',
                'name' => 'Printer Epson EcoTank L6290 WiFi Duplex All-in-One',
                'category' => 'Perangkat Kantor & Cetak',
                'subcategory' => 'Printer Warna',
                'edition' => 'ADF + WiFi Direct',
                'room' => 'Ruang Sekretariat & Umum',
                'stock' => 9,
                'price' => 6250000,
                'status' => 'active',
                'description' => 'Printer ink-tank multifungsi untuk cetak dokumen laporan, surat dinas, dan pemindaian berkas.',
            ],
            [
                'kode_barang' => 'DKI-GRT-2026-016',
                'barcode' => 'BRG-000016',
                'name' => 'Scanner Dokumen Fujitsu ScanSnap iX1600 High-Speed',
                'category' => 'Perangkat Kantor & Cetak',
                'subcategory' => 'Scanner Arsip',
                'edition' => '40 ppm Duplex',
                'room' => 'Ruang Sekretariat & Umum',
                'stock' => 5,
                'price' => 8750000,
                'status' => 'active',
                'description' => 'Scanner digitalisasi dokumen arsip surat masuk/keluar dan berkas berita acara serah terima aset.',
            ],
            [
                'kode_barang' => 'DKI-GRT-2026-017',
                'barcode' => 'BRG-000017',
                'name' => 'UPS APC Smart-UPS 3000VA LCD RM 2U (SMT3000RMI2U)',
                'category' => 'Perangkat Kantor & Cetak',
                'subcategory' => 'Catu Daya Cadangan',
                'edition' => 'Rackmount 3000VA',
                'room' => 'Ruang Server & NOC (Data Center Lt. 2)',
                'stock' => 6,
                'price' => 24500000,
                'status' => 'active',
                'description' => 'Unit UPS rackmount pelindung server dan core router dari gangguan listrik mendadak.',
            ],
            [
                'kode_barang' => 'DKI-GRT-2026-018',
                'barcode' => 'BRG-000018',
                'name' => 'Proyektor Laser Epson EB-L200F Full HD 4500 Lumens',
                'category' => 'Perangkat Kantor & Cetak',
                'subcategory' => 'Proyektor Presentasi',
                'edition' => 'Wireless Miracast',
                'room' => 'Ruang Bidang Aplikasi Informatika (Aptika)',
                'stock' => 4,
                'price' => 19200000,
                'status' => 'active',
                'description' => 'Proyektor laser resolusi 1080p untuk ruang rapat teknis dan bimbingan teknis operator OPD.',
            ],
            [
                'kode_barang' => 'DKI-GRT-2026-019',
                'barcode' => 'BRG-000019',
                'name' => 'Hardware Next-Gen Firewall FortiGate 100F Appliance',
                'category' => 'Keamanan Siber & Persandian',
                'subcategory' => 'Network Security',
                'edition' => 'UTM Bundle Enterprise',
                'room' => 'Ruang Server & NOC (Data Center Lt. 2)',
                'stock' => 2,
                'price' => 68000000,
                'status' => 'active',
                'description' => 'Perangkat firewall utama proteksi trafik server Pemkab Garut dan mitigasi serangan siber CSIRT.',
            ],
            [
                'kode_barang' => 'DKI-GRT-2026-020',
                'barcode' => 'BRG-000020',
                'name' => 'HT Digital Terenkripsi Motorola XiR P6620i VHF/UHF',
                'category' => 'Keamanan Siber & Persandian',
                'subcategory' => 'Radio Komunikasi Sandi',
                'edition' => 'AES-256 Digital',
                'room' => 'Ruang Bidang Statistik & Persandian',
                'stock' => 10,
                'price' => 5800000,
                'status' => 'active',
                'description' => 'Radio handy talky digital terenkripsi untuk dukungan komunikasi pimpinan dan tim pengamanan informasi.',
            ],
            [
                'kode_barang' => 'DKI-GRT-2026-021',
                'barcode' => 'BRG-000021',
                'name' => 'Harddisk Eksternal Backup WD My Book Enterprise 8TB',
                'category' => 'Keamanan Siber & Persandian',
                'subcategory' => 'Media Penyimpanan Backup',
                'edition' => 'USB 3.2 Hardware Encryption',
                'room' => 'Ruang Bidang Statistik & Persandian',
                'stock' => 7,
                'price' => 4150000,
                'status' => 'active',
                'description' => 'Media penyimpanan cold-backup database periodik dan salinan dokumen terenkripsi.',
            ],
            [
                'kode_barang' => 'DKI-GRT-2026-022',
                'barcode' => 'BRG-000022',
                'name' => 'Kursi Kerja Ergonomis Jaring Hidrolik Staff TIK',
                'category' => 'Furnitur & Sarana Ruang Kerja',
                'subcategory' => 'Kursi Kerja',
                'edition' => 'Tipe Executive Mesh',
                'room' => 'Ruang Bidang Aplikasi Informatika (Aptika)',
                'stock' => 30,
                'price' => 1850000,
                'status' => 'active',
                'description' => 'Kursi kerja ergonomis berbahan mesh dengan penyangga lumbar untuk staf operasional Diskominfo.',
            ],
            [
                'kode_barang' => 'DKI-GRT-2026-023',
                'barcode' => 'BRG-000023',
                'name' => 'Lemari Arsip Besi Pintu Geser Kaca Brother B-204',
                'category' => 'Furnitur & Sarana Ruang Kerja',
                'subcategory' => 'Lemari Arsip',
                'edition' => 'Plat Besi 0.8mm',
                'room' => 'Ruang Sekretariat & Umum',
                'stock' => 8,
                'price' => 3650000,
                'status' => 'active',
                'description' => 'Lemari penyimpanan dokumen kontrak, BAST aset, dan arsip kepegawaian.',
            ],
            [
                'kode_barang' => 'DKI-GRT-2026-024',
                'barcode' => 'BRG-000024',
                'name' => 'Kabel Patch Cord Fiber Optik Duplex LC-LC 10 Meter',
                'category' => 'Perangkat Jaringan & Server',
                'subcategory' => 'Kabel & Aksesoris',
                'edition' => 'Single Mode OS2',
                'room' => 'Gudang Aset & Logistik TIK',
                'stock' => 0,
                'price' => 125000,
                'status' => 'out_of_stock',
                'description' => 'Kabel patch cord fiber optik cadangan interkoneksi rak server (stok habis terpakai instalasi baru, menunggu restock).',
            ],
            [
                'kode_barang' => 'DKI-GRT-2026-025',
                'barcode' => 'BRG-000025',
                'name' => 'Optical Time Domain Reflectometer (OTDR) Joinwit JW3302',
                'category' => 'Perangkat Jaringan & Server',
                'subcategory' => 'Alat Ukur Jaringan',
                'edition' => '1310/1550nm Fiber Tester',
                'room' => 'Gudang Aset & Logistik TIK',
                'stock' => 1,
                'price' => 14500000,
                'status' => 'inactive',
                'description' => 'Alat deteksi titik putus kabel fiber optik jaringan FO Pemkab (sedang dalam proses kalibrasi berkala).',
            ],
        ];

        $seededProducts = [];
        foreach ($productsData as $item) {
            $catModel = $categories[$item['category']] ?? null;
            $roomModel = $rooms[$item['room']] ?? null;

            $product = Product::updateOrCreate(
                ['kode_barang' => $item['kode_barang']],
                [
                    'barcode' => $item['barcode'],
                    'name' => $item['name'],
                    'category_id' => $catModel?->id,
                    'category' => $item['category'],
                    'subcategory' => $item['subcategory'],
                    'edition' => $item['edition'],
                    'room_id' => $roomModel?->id,
                    'stock' => $item['stock'],
                    'price' => $item['price'],
                    'status' => $item['status'],
                    'description' => $item['description'],
                ]
            );
            $seededProducts[$item['kode_barang']] = $product;
        }

        // 4. Seed Sample Mutations & Activity Logs (only when empty to stay idempotent)
        if (Mutation::count() === 0) {
            $adminUser = User::whereIn('role', ['superadmin', 'admin'])->first();
            $operatorUser = User::where('role', 'user')->first() ?? $adminUser;

            if ($adminUser && isset($seededProducts['DKI-GRT-2026-004'], $seededProducts['DKI-GRT-2026-006'], $seededProducts['DKI-GRT-2026-010'])) {
                Mutation::create([
                    'product_id' => $seededProducts['DKI-GRT-2026-004']->id,
                    'from_room_id' => $rooms['Gudang Aset & Logistik TIK']->id,
                    'to_room_id' => $rooms['Garut Command Center (GCC)']->id,
                    'user_id' => $adminUser->id,
                    'type' => 'pindah_ruang',
                    'quantity' => 2,
                    'mutation_date' => now()->subDays(5)->toDateString(),
                    'status' => 'approved',
                    'approved_by' => $adminUser->id,
                    'approved_at' => now()->subDays(5),
                    'note' => 'Penambahan 2 titik Access Point WiFi 6 untuk area rapat Garut Command Center.',
                ]);

                Mutation::create([
                    'product_id' => $seededProducts['DKI-GRT-2026-006']->id,
                    'from_room_id' => null,
                    'to_room_id' => $rooms['Ruang Bidang Aplikasi Informatika (Aptika)']->id,
                    'user_id' => $adminUser->id,
                    'type' => 'masuk',
                    'quantity' => 4,
                    'mutation_date' => now()->subDays(3)->toDateString(),
                    'status' => 'approved',
                    'approved_by' => $adminUser->id,
                    'approved_at' => now()->subDays(3),
                    'note' => 'Serah terima pengadaan Laptop Kerja Tim Pengembang SPBE.',
                ]);

                Mutation::create([
                    'product_id' => $seededProducts['DKI-GRT-2026-010']->id,
                    'from_room_id' => $rooms['Ruang Bidang Informasi & Komunikasi Publik (IKP)']->id,
                    'to_room_id' => $rooms['Studio Podcast & Media Center Diskominfo']->id,
                    'user_id' => $operatorUser->id,
                    'type' => 'pindah_ruang',
                    'quantity' => 1,
                    'mutation_date' => now()->toDateString(),
                    'status' => 'pending',
                    'note' => 'Permohonan peminjaman 1 unit Kamera Sony A7 IV untuk rekaman podcast mingguan di Studio Media Center.',
                ]);
            }
        }

        if (ActivityLog::count() === 0 && ! empty($seededProducts)) {
            $adminUser = User::whereIn('role', ['superadmin', 'admin'])->first();
            $firstProduct = reset($seededProducts);
            ActivityLog::create([
                'user_id' => $adminUser?->id,
                'action' => 'created',
                'subject_type' => Product::class,
                'subject_id' => $firstProduct->id,
                'description' => 'Inisialisasi katalog data barang inventaris Diskominfo Kabupaten Garut (25 aset terdaftar).',
            ]);
        }
    }
}
