<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Penanda: layanan ini melakukan DETEKSI AI.
 *
 * Alat deteksi AI hanya andal untuk teks berbahasa Inggris, jadi dokumen
 * yang bukan Inggris ditolak sejak diunggah — daripada diproses lalu
 * hasilnya keliru. Ditandai admin per produk & per add-on, bukan ditebak
 * dari nama, supaya sistem tidak salah kira.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->boolean('cek_ai')->default(false)->after('pakai_exclude');
        });

        Schema::table('product_addons', function (Blueprint $table) {
            $table->boolean('cek_ai')->default(false)->after('pakai_exclude');
        });
    }

    public function down(): void
    {
        Schema::table('products', fn (Blueprint $t) => $t->dropColumn('cek_ai'));
        Schema::table('product_addons', fn (Blueprint $t) => $t->dropColumn('cek_ai'));
    }
};
