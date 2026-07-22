<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mutations', function (Blueprint $table): void {
            if (!Schema::hasColumn('mutations', 'movement_type')) {
                $table->string('movement_type')->default('incoming');
            }

            if (!Schema::hasColumn('mutations', 'quantity')) {
                $table->integer('quantity')->default(0);
            }
        });
    }

    public function down(): void
    {
        Schema::table('mutations', function (Blueprint $table): void {
            $table->dropColumn(['movement_type', 'quantity']);
        });
    }
};
