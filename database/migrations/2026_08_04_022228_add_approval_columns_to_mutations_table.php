<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('mutations', function (Blueprint $table) {
            $table->enum('status', ['pending', 'approved', 'rejected'])
                  ->default('pending')
                  ->after('note');

            $table->text('rejection_note')->nullable()->after('status');

            $table->foreignId('approved_by')
                  ->nullable()
                  ->after('rejection_note')
                  ->constrained('users')
                  ->nullOnDelete();

            $table->timestamp('approved_at')->nullable()->after('approved_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mutations', function (Blueprint $table) {
            $table->dropForeign(['approved_by']);
            $table->dropColumn(['status', 'rejection_note', 'approved_by', 'approved_at']);
        });
    }
};