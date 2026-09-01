<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (DB::connection()->getDriverName() === 'sqlite') {
            DB::table('users')
                ->where(function ($query) {
                    $query->whereNotIn('role', ['superadmin', 'admin', 'user'])
                          ->orWhereNull('role');
                })
                ->update(['role' => 'user']);

            return;
        }

        // Bersihkan dulu data role yang tidak valid (misal "administrator")
        // sebelum enum baru diterapkan, supaya tidak gagal "Data truncated"
        DB::table('users')
            ->where(function ($query) {
                $query->whereNotIn('role', ['admin', 'user'])
                      ->orWhereNull('role');
            })
            ->update(['role' => 'user']);

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('superadmin', 'admin', 'user') NOT NULL DEFAULT 'user'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'user') NOT NULL DEFAULT 'user'");
        }
    }
};