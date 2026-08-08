<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Penjadwalan tayang banner (seperti Promo).
 *
 * Keduanya NULLABLE dan itu disengaja: banner lama otomatis bernilai null =
 * "tanpa batas waktu", sehingga perilakunya sama persis seperti sebelum ada
 * fitur ini. Tidak ada banner yang tiba-tiba hilang dari beranda setelah
 * migrasi dijalankan.
 *
 * - mulai_tayang  null = tayang sejak kapan pun (tidak menunggu)
 * - selesai_tayang null = tayang sampai kapan pun (tidak berakhir)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('banners', function (Blueprint $table) {
            $table->dateTime('mulai_tayang')->nullable()->after('status');
            $table->dateTime('selesai_tayang')->nullable()->after('mulai_tayang');

            // Beranda menyaring banner pada tiap kunjungan; indeks menjaga
            // penyaringan itu tetap murah saat banner bertambah banyak.
            $table->index(['status', 'mulai_tayang', 'selesai_tayang'], 'banners_tayang_index');
        });
    }

    public function down(): void
    {
        Schema::table('banners', function (Blueprint $table) {
            $table->dropIndex('banners_tayang_index');
            $table->dropColumn(['mulai_tayang', 'selesai_tayang']);
        });
    }
};
