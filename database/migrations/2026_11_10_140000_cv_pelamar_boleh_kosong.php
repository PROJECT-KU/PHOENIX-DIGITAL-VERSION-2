<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * cv_path boleh kosong.
 *
 * Bukan karena lamaran boleh dikirim tanpa CV — formulirnya tetap
 * mewajibkannya. Melainkan karena berkasnya kelak DIHAPUS oleh
 * `pelamar:hapus-berkas-kadaluarsa`, dan catatan lamarannya tetap disimpan.
 *
 * Tanpa ini, satu-satunya cara menghapus CV adalah ikut menghapus catatan
 * lamarannya — padahal justru catatan itu yang masih berguna bertahun-tahun
 * kemudian ("dulu si A pernah melamar belum?"), dan justru berkasnya yang
 * tidak boleh disimpan selamanya.
 *
 * cover_letter_path sudah nullable sejak 2026_11_10_090000; yang tertinggal
 * hanya cv_path.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tbl_job_applications', function (Blueprint $table) {
            $table->string('cv_path')->nullable()->change();
        });
    }

    public function down(): void
    {
        /*
         | TIDAK dikembalikan jadi NOT NULL.
         |
         | Begitu pemangkas berjalan sekali, ada baris yang cv_path-nya kosong
         | dan tidak mungkin diisi kembali — berkasnya sudah tidak ada.
         | Mengembalikan batasannya akan menggagalkan migrasi turun di tengah
         | jalan, atau lebih buruk, memaksa baris itu diisi nilai karangan.
         */
    }
};
