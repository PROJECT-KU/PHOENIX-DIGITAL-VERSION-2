<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Menyatakan TEGAS jenis pekerjaan sebuah add-on, menggantikan penebakan.
 *
 * Sebelumnya jenisnya disimpulkan dari `pakai_exclude` dan `cek_ai`. Itu
 * keliru: pada add-on jaminan "Plagiasi di bawah 20% maksimal 5%",
 * pakai_exclude berarti "pengecekannya memakai setelan exclude" — BUKAN
 * "ini satu pengecekan plagiasi tersendiri". Akibatnya jaminan seharga
 * Rp100.000 itu menambah satu kuota hantu yang tak pernah bisa dipakai, dan
 * pesanan tidak pernah tuntas sendiri (INV-20260831-0005, 4 pesanan aktif).
 *
 * Sekarang kolomnya diisi sengaja, dan kosong berarti "tidak menambah
 * pekerjaan" — nilai bawaan yang aman untuk add-on baru.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_addons', function (Blueprint $table) {
            $table->string('jenis_layanan', 20)->default('')->after('cek_ai');
        });

        // Add-on yang memang menghasilkan berkas untuk pelanggan.
        DB::table('product_addons')->where('cek_ai', 1)->update(['jenis_layanan' => 'ai']);
        DB::table('product_addons')->where('nama', 'like', '%AI%')->update(['jenis_layanan' => 'ai']);
        DB::table('product_addons')->where('nama', 'like', '%Turnitin%')->update(['jenis_layanan' => 'plagiasi']);

        // Jaminan mutu: tidak menghasilkan berkas terpisah, jadi tidak menambah
        // pekerjaan. Dicocokkan dari namanya karena itulah yang membedakannya.
        DB::table('product_addons')->where('nama', 'like', '%di bawah%')->update(['jenis_layanan' => '']);
    }

    public function down(): void
    {
        Schema::table('product_addons', function (Blueprint $table) {
            $table->dropColumn('jenis_layanan');
        });
    }
};
