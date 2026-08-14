<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Penjadwalan tayang untuk paket bundling.
 *
 * Kebutuhannya: paket promosi seperti "Bundling Maulid Nabi" harus MUNCUL
 * sendiri saat tanggalnya tiba dan HILANG sendiri saat lewat — bukan produk
 * tetap yang menunggu admin ingat mematikannya.
 *
 * Sebelum ini toko hanya menyaring `status = active`, sehingga paket musiman
 * menetap sampai ada yang menonaktifkannya manual.
 *
 * Keduanya nullable dan berarti "tanpa batas": seluruh bundling yang sudah ada
 * tetap tampil persis seperti sebelumnya tanpa perlu disentuh.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_bundlings', function (Blueprint $table) {
            $table->timestamp('mulai_tayang')->nullable()->after('status');
            $table->timestamp('selesai_tayang')->nullable()->after('mulai_tayang');
        });
    }

    public function down(): void
    {
        Schema::table('product_bundlings', function (Blueprint $table) {
            $table->dropColumn(['mulai_tayang', 'selesai_tayang']);
        });
    }
};
