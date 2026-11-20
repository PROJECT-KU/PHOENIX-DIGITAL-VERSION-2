<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Siapa karyawan yang menangani surat ini.
 *
 * BERBEDA dari disposisi, dan dua-duanya tetap dibutuhkan. Disposisi adalah
 * perintah: bisa beberapa, punya tenggat, dan ditutup satu per satu. Kolom
 * ini adalah PEMILIK urusannya — satu nama yang ditanya lebih dulu ketika
 * orang bertanya "surat ini sekarang di siapa?", bahkan sebelum ada perintah
 * apa pun yang dikeluarkan.
 *
 * Menunjuk users, bukan menyalin namanya: karyawan berganti nama (menikah,
 * gelar baru) dan arsip harus ikut berganti, bukan menyimpan nama lama
 * selamanya. nullOnDelete supaya akun yang dihapus tidak menyeret suratnya.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('surat', function (Blueprint $t) {
            $t->foreignId('penanggung_jawab_id')->nullable()->after('dicatat_oleh')
                ->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('surat', function (Blueprint $t) {
            $t->dropConstrainedForeignId('penanggung_jawab_id');
        });
    }
};
