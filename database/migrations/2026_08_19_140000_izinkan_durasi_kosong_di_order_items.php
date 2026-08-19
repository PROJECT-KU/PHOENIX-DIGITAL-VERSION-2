<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Paket bundling tidak punya durasi tunggal — durasinya melekat pada tiap
 * produk di dalam paket. Sebelumnya `duration_type` dan `duration_value`
 * NOT NULL, sehingga setiap checkout paket gagal saat menyimpan order item:
 * pesanan dibatalkan diam-diam dan pembeli tidak pernah sampai ke halaman QRIS.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->enum('duration_type', ['bulan', 'tahun', 'sekali', 'kali', 'halaman'])
                ->nullable()
                ->default(null)
                ->change();
            $table->integer('duration_value')->nullable()->default(null)->change();
        });
    }

    public function down(): void
    {
        // Baris paket dikosongkan dulu agar kolomnya bisa dikembalikan NOT NULL.
        Schema::table('order_items', function (Blueprint $table) {
            $table->enum('duration_type', ['bulan', 'tahun', 'sekali', 'kali', 'halaman'])
                ->default('bulan')
                ->nullable(false)
                ->change();
            $table->integer('duration_value')->nullable(false)->change();
        });
    }
};
