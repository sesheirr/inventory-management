<?php

use App\Models\Product;
use App\Models\Room;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('app:migrate-room-to-room-id', function () {
    $productsToMigrate = Product::whereNull('room_id')
        ->whereNotNull('room')
        ->where('room', '<>', '')
        ->cursor();

    $migratedCount = 0;
    $createdRooms = 0;
    $skippedEmptyRoom = 0;

    foreach ($productsToMigrate as $product) {
        $normalizedRoomName = trim(preg_replace('/\s+/', ' ', mb_strtolower($product->room ?? '')));

        if ($normalizedRoomName === '') {
            $skippedEmptyRoom++;
            continue;
        }

        $normalizedRoomName = mb_convert_case($normalizedRoomName, MB_CASE_TITLE, 'UTF-8');

        $room = Room::firstOrCreate([
            'name' => $normalizedRoomName,
        ]);

        if ($room->wasRecentlyCreated) {
            $createdRooms++;
        }

        $product->room_id = $room->id;
        $product->save();
        $migratedCount++;
    }

    $this->info('Migration selesai.');
    $this->info("Produk diproses: {$migratedCount}");
    $this->info("Ruangan baru dibuat: {$createdRooms}");
    $this->info("Produk dilewati karena nilai room kosong setelah normalisasi: {$skippedEmptyRoom}");
    $this->info('Silakan verifikasi hasil di tabel products dan rooms sebelum menghapus kolom room lama.');
})->purpose('Migrate legacy product room text into room_id foreign key');
