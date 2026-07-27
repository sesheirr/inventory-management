<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mutations', function (Blueprint $table): void {
            if (Schema::hasColumn('mutations', 'movement_type') && !Schema::hasColumn('mutations', 'type')) {
                $table->enum('type', ['masuk', 'keluar', 'pindah_ruang'])->default('masuk');
            }

            if (!Schema::hasColumn('mutations', 'quantity')) {
                $table->integer('quantity')->default(0);
            }
        });

        if (Schema::hasColumn('mutations', 'movement_type') && Schema::hasColumn('mutations', 'type')) {
            DB::table('mutations')
                ->where('movement_type', 'incoming')
                ->update(['type' => 'masuk']);
            DB::table('mutations')
                ->where('movement_type', 'outgoing')
                ->update(['type' => 'keluar']);
            DB::table('mutations')
                ->where('movement_type', 'move')
                ->update(['type' => 'pindah_ruang']);
            DB::table('mutations')
                ->where('movement_type', 'masuk')
                ->update(['type' => 'masuk']);
            DB::table('mutations')
                ->where('movement_type', 'keluar')
                ->update(['type' => 'keluar']);
            DB::table('mutations')
                ->where('movement_type', 'pindah_ruang')
                ->update(['type' => 'pindah_ruang']);

            Schema::table('mutations', function (Blueprint $table): void {
                $table->dropColumn('movement_type');
            });
        }
    }

    public function down(): void
    {
        Schema::table('mutations', function (Blueprint $table): void {
            if (Schema::hasColumn('mutations', 'movement_type')) {
                $table->dropColumn('movement_type');
            }

            if (Schema::hasColumn('mutations', 'type')) {
                $table->dropColumn('type');
            }

            if (Schema::hasColumn('mutations', 'quantity')) {
                $table->dropColumn('quantity');
            }
        });
    }
};
