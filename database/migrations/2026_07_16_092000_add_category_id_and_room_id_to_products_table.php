<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tambahkan kolom name jika belum ada
        Schema::table('categories', function (Blueprint $table): void {

            if (!Schema::hasColumn('categories', 'name')) {
                $table->string('name')->after('id');
            }

            if (!Schema::hasColumn('categories', 'description')) {
                $table->text('description')->nullable()->after('name');
            }

        });


        // Tambahkan relasi ke products
        Schema::table('products', function (Blueprint $table): void {

            if (!Schema::hasColumn('products', 'category_id')) {
                $table->foreignId('category_id')
                    ->nullable()
                    ->constrained('categories')
                    ->nullOnDelete();
            }

            if (!Schema::hasColumn('products', 'room_id')) {
                $table->foreignId('room_id')
                    ->nullable()
                    ->constrained('rooms')
                    ->nullOnDelete();
            }

        });


        // Pindahkan data category lama ke tabel categories
        DB::transaction(function () {

            $categories = DB::table('products')
                ->select('category')
                ->whereNotNull('category')
                ->where('category', '<>', '')
                ->distinct()
                ->pluck('category');


            $existingCategories = DB::table('categories')
                ->whereIn('name', $categories->all())
                ->pluck('id', 'name')
                ->toArray();


            foreach ($categories as $categoryName) {

                if (!array_key_exists($categoryName, $existingCategories)) {

                    $existingCategories[$categoryName] = DB::table('categories')->insertGetId([
                        'name' => $categoryName,
                        'description' => null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                }
            }


            foreach ($existingCategories as $categoryName => $categoryId) {

                DB::table('products')
                    ->where('category', $categoryName)
                    ->update([
                        'category_id' => $categoryId
                    ]);

            }

        });

    }


    public function down(): void
    {
        Schema::table('products', function (Blueprint $table): void {

            if (Schema::hasColumn('products', 'category_id')) {
                $table->dropForeign(['category_id']);
                $table->dropColumn('category_id');
            }

            if (Schema::hasColumn('products', 'room_id')) {
                $table->dropForeign(['room_id']);
                $table->dropColumn('room_id');
            }

        });


        Schema::table('categories', function (Blueprint $table): void {

            if (Schema::hasColumn('categories', 'description')) {
                $table->dropColumn('description');
            }

            if (Schema::hasColumn('categories', 'name')) {
                $table->dropColumn('name');
            }

        });
    }
};