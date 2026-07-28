<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Buat kolom `path` (berkas unggahan customer) nullable.
 *
 * Berkas jasa dihapus otomatis 7 hari setelah link /cek kedaluwarsa
 * (command jasa:hapus-berkas-kadaluarsa). Saat berkas dihapus, kolom path
 * di-null-kan supaya UI tak menawarkan unduhan yang berkasnya sudah tiada.
 * Kolom hasil_* sudah nullable; hanya `path` yang dulu NOT NULL.
 *
 * Aditif: insert unggahan baru tetap mengisi path seperti biasa; migrasi ini
 * hanya MENGIZINKAN null, tidak mengubah alur mana pun.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_uploads', function (Blueprint $table) {
            $table->string('path')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('order_uploads', function (Blueprint $table) {
            $table->string('path')->nullable(false)->change();
        });
    }
};
