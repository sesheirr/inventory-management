<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mutations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('type', ['masuk', 'keluar', 'pindah_ruang'])->default('masuk');
            $table->integer('quantity')->default(0);
            $table->foreignId('from_room_id')->nullable()->constrained('rooms')->nullOnDelete();
            $table->foreignId('to_room_id')->nullable()->constrained('rooms')->nullOnDelete();
            $table->dateTime('mutation_date');
            $table->text('note')->nullable();
            $table->timestamps();
            $table->index(['type', 'mutation_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mutations');
    }
};