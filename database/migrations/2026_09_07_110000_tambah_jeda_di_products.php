<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Jeda per produk — menutup tombol beli tanpa menyembunyikan produknya.
 *
 * Layanan jasa dijeda per JENIS karena pengelompokannya alami. Produk akun
 * tidak punya pengelompokan seperti itu: yang bermasalah biasanya satu produk
 * saja (penyedianya sedang kacau, atau akunnya kerap kena banned), sementara
 * puluhan produk lain baik-baik saja.
 *
 * Disimpan sebagai kolom, bukan baris `settings`, supaya memeriksa satu produk
 * tidak memerlukan kueri tersendiri saat halaman toko dirender.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->boolean('dijeda')->default(false)->after('deskripsi');
            $table->string('pesan_jeda', 200)->nullable()->after('dijeda');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['dijeda', 'pesan_jeda']);
        });
    }
};
