<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Penanda: layanan ini memakai pilihan "Kecualikan dari pemeriksaan".
 *
 * Exclude (daftar pustaka / kutipan / source) hanya relevan untuk pengecekan
 * KEMIRIPAN. Pengecekan AI tidak memakainya — teks tetap dinilai utuh. Jadi
 * pilihannya hanya ditampilkan bila layanan (atau add-on yang dibeli customer)
 * memang pengecekan plagiasi.
 *
 * Dibuat sebagai kolom yang bisa diatur admin, bukan ditebak dari nama produk,
 * supaya produk baru tetap terkendali.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Default true = perilaku lama (cek plagiasi) tidak berubah.
            $table->boolean('pakai_exclude')->default(true)->after('addon_mode');
        });

        Schema::table('product_addons', function (Blueprint $table) {
            $table->boolean('pakai_exclude')->default(false)->after('harga');
        });

        // Produk non-jasa tak pernah memakai exclude — rapikan sekalian.
        DB::table('products')->where('butuh_file', false)->update(['pakai_exclude' => false]);

        // Jasa PER HALAMAN (parafrase) punya alur pengecualiannya sendiri di
        // halaman produk, bukan lewat panel exclude di /cek.
        DB::table('products')->where('butuh_file', true)->where('jasa_mode', 'halaman')
            ->update(['pakai_exclude' => false]);
    }

    public function down(): void
    {
        Schema::table('products', fn (Blueprint $t) => $t->dropColumn('pakai_exclude'));
        Schema::table('product_addons', fn (Blueprint $t) => $t->dropColumn('pakai_exclude'));
    }
};
