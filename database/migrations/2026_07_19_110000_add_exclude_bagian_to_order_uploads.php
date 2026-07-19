<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Pengecualian khusus jasa PARAFRASE: bagian dokumen yang tak perlu
     * diparafrase (cover, daftar isi, daftar pustaka) + nomor halaman yang
     * dipilih customer. Disalin ke pengecekan agar tim melihat instruksinya
     * lengkap di satu tempat.
     *
     * Aditif: default false / null, jadi jasa cek plagiasi tak berubah.
     */
    public function up(): void
    {
        Schema::table('order_uploads', function (Blueprint $table) {
            $table->boolean('exclude_cover')->default(false)->after('exclude_kutipan');
            $table->boolean('exclude_daftar_isi')->default(false)->after('exclude_cover');
            $table->string('halaman_dikecualikan')->nullable()->after('exclude_daftar_isi');
        });
    }

    public function down(): void
    {
        Schema::table('order_uploads', function (Blueprint $table) {
            $table->dropColumn(['exclude_cover', 'exclude_daftar_isi', 'halaman_dikecualikan']);
        });
    }
};
