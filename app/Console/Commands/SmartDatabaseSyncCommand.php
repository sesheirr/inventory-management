<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\Mutation;
use App\Models\Product;
use App\Models\Room;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\ProductSeeder;
use Database\Seeders\SuperAdminSeeder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Throwable;

class SmartDatabaseSyncCommand extends Command
{
    protected $signature = 'inventory:smart-db-sync
                            {--strategy=auto : Strategi sinkronisasi (auto, seed, populate-keep, replace-irrelevant)}';

    protected $description = 'Deteksi pintar kondisi database lokal (kosong, butuh seed, data sedikit, atau data tidak relevan) dan sinkronkan otomatis.';

    public function handle(): int
    {
        $this->components->info('Memeriksa kondisi struktur & data database lokal (Smart DB Sync)...');

        try {
            DB::connection()->getPdo();
        } catch (Throwable $e) {
            $this->components->error('Koneksi database gagal: '.$e->getMessage());

            return self::FAILURE;
        }

        $coreTables = ['users', 'categories', 'rooms', 'products', 'mutations'];
        $missingTables = [];
        foreach ($coreTables as $table) {
            if (! Schema::hasTable($table)) {
                $missingTables[] = $table;
            }
        }

        // 1. Jika tabel inti belum ada (kosong banget) -> jalankan migrate + seed penuh
        if (! empty($missingTables)) {
            $this->components->warn('Database belum termigrasi (tabel belum lengkap: '.implode(', ', $missingTables).'). Menjalankan migrate + seed...');
            Artisan::call('migrate', ['--force' => true], $this->getOutput());
            Artisan::call('db:seed', ['--force' => true, '--class' => DatabaseSeeder::class], $this->getOutput());
            $this->renderSummary('EMPTY_DB_MIGRATED_AND_SEEDED');

            return self::SUCCESS;
        }

        // Pastikan migrasi baru (mis. Telescope / kolom baru) selalu dijalankan dengan aman
        Artisan::call('migrate', ['--force' => true]);

        $forcedStrategy = strtolower((string) $this->option('strategy'));

        $userCount = User::count();
        $categoryCount = Category::count();
        $roomCount = Room::count();
        $productCount = Product::count();

        // 2. Jika sudah termigrasi tapi data inventaris / user masih 0 -> jalankan seed
        if ($forcedStrategy === 'seed' || ($userCount === 0 && $productCount === 0) || ($productCount === 0 && $categoryCount === 0 && $roomCount === 0)) {
            $this->components->warn('Database sudah termigrasi namun data masih kosong. Menjalankan seeder Diskominfo...');
            Artisan::call('db:seed', ['--force' => true, '--class' => DatabaseSeeder::class], $this->getOutput());
            $this->renderSummary('SEED_ONLY_APPLIED');

            return self::SUCCESS;
        }

        // 3. Cek apakah terdapat data yang tidak relevan / placeholder / korup
        $irrelevantProductsCount = Product::query()
            ->where(function ($q) {
                $q->where('name', 'like', 'Product %')
                    ->orWhere('name', 'like', 'Dummy %')
                    ->orWhere('name', 'like', 'Lorem %')
                    ->orWhere('description', 'like', '%modern store operations%')
                    ->orWhereNull('category_id')
                    ->orWhereNull('room_id');
            })
            ->count();

        $officialDiskominfoCount = Product::query()
            ->where('kode_barang', 'like', 'DKI-GRT-%')
            ->count();

        $hasIrrelevantData = $forcedStrategy === 'replace-irrelevant'
            || $irrelevantProductsCount > 0
            || ($productCount > 0 && $officialDiskominfoCount === 0);

        if ($hasIrrelevantData) {
            $this->components->warn('Terdeteksi data inventaris tidak relevan/placeholder. Menjalankan strategi: Keep Identity (Users) + Replace Inventory Data with Official Seeds...');

            Schema::disableForeignKeyConstraints();
            try {
                Mutation::query()->delete();
                Product::query()->delete();
                Room::query()->delete();
                Category::query()->delete();
            } finally {
                Schema::enableForeignKeyConstraints();
            }

            // Pastikan akun inti tetap tersedia tanpa menghapus user existing (Keep Identity)
            Artisan::call('db:seed', ['--force' => true, '--class' => SuperAdminSeeder::class]);
            Artisan::call('db:seed', ['--force' => true, '--class' => ProductSeeder::class]);
            $this->renderSummary('KEEP_IDENTITY_REPLACED_IRRELEVANT_DATA');

            return self::SUCCESS;
        }

        // 4. Cek apakah data sedikit (sparse) -> Populate Seed + Keep Existing
        $isSparseData = $forcedStrategy === 'populate-keep'
            || $productCount < 15
            || $roomCount < 6
            || $categoryCount < 5
            || $userCount < 3;

        if ($isSparseData) {
            $this->components->info("Terdeteksi data lokal masih sedikit (Barang: {$productCount}, Ruangan: {$roomCount}, Kategori: {$categoryCount}). Menjalankan Populate Seed + Keep Existing...");
            Artisan::call('db:seed', ['--force' => true, '--class' => DatabaseSeeder::class]);
            $this->renderSummary('SPARSE_DATA_POPULATED_KEEP_EXISTING');

            return self::SUCCESS;
        }

        // 5. Data sudah lengkap dan relevan -> pastikan akun SuperAdmin tetap sinkron
        Artisan::call('db:seed', ['--force' => true, '--class' => SuperAdminSeeder::class]);
        $this->renderSummary('HEALTHY_EXISTING_DATA_KEPT');

        return self::SUCCESS;
    }

    private function renderSummary(string $strategyCode): void
    {
        $this->components->twoColumnDetail('Strategi Sinkronisasi', $strategyCode);
        $this->components->twoColumnDetail('Total Pengguna (Identity)', (string) User::count());
        $this->components->twoColumnDetail('Total Barang (Products)', (string) Product::count());
        $this->components->twoColumnDetail('Total Ruangan & Kategori', Room::count().' ruangan / '.Category::count().' kategori');
        $this->components->twoColumnDetail('Total Transaksi Mutasi', (string) Mutation::count());
    }
}
