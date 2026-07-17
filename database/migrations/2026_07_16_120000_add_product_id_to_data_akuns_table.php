<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tautkan Data Akun ke Produk induknya.
 *
 * Kenapa: nama akun berpola "Produk + nomor" (mis. "Grammarly 1"–"Grammarly 10",
 * "Scopus Private 1"). Menebak produk dari NAMA itu rapuh — satu typo/spasi/huruf
 * besar dan tebakannya meleset, padahal ini dipakai menghitung uang.
 *
 * Dengan tautan ini:
 *  - sifat private/sharing IKUT produknya (tidak perlu penanda ganda yg bisa bentrok)
 *  - harga modal private diambil dari katalog Harga Modal yg SAMA dgn Order biasa
 *  - penjualan RSC bisa diakui per produk di Omset Bersih
 *
 * Sengaja NULLABLE: akun lama yang belum ditautkan tetap jalan dan dianggap
 * bukan-private, jadi tidak ada entri uang yang salah tercatat.
 * nullOnDelete: produk dihapus → tautan dikosongkan, Data Akun tidak ikut hilang.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('data_akuns', function (Blueprint $table) {
            $table->uuid('product_id')->nullable()->after('nama_akun');

            $table->foreign('product_id')
                ->references('id')->on('products')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('data_akuns', function (Blueprint $table) {
            $table->dropForeign(['product_id']);
            $table->dropColumn('product_id');
        });
    }
};
