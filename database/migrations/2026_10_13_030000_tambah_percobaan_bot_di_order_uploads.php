<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Percobaan ulang otomatis bot cek plagiasi.
 *
 * Sebelumnya tiap kegagalan menunggu admin menekan "Coba Lagi pakai Bot",
 * sehingga pesanan bisa menggantung berjam-jam kalau tidak terpantau. Dua
 * kolom ini yang membuat percobaan ulang bisa dibatasi dengan aman:
 *
 * - `bot_percobaan`  : sudah berapa kali bot mengambil unggahan ini. Dibatasi
 *                      supaya satu berkas bermasalah tidak dicoba selamanya.
 * - `bot_tunggu_admin`: kegagalan yang JELAS butuh manusia (koin kurang, sesi
 *                      penyedia habis, saringan berangka yang tidak ada
 *                      padanannya). Tidak pernah dicoba ulang sendiri.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_uploads', function (Blueprint $table) {
            $table->unsignedTinyInteger('bot_percobaan')->default(0)->after('bot_status');
            $table->boolean('bot_tunggu_admin')->default(false)->after('bot_percobaan');
        });
    }

    public function down(): void
    {
        Schema::table('order_uploads', function (Blueprint $table) {
            $table->dropColumn(['bot_percobaan', 'bot_tunggu_admin']);
        });
    }
};
