<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Halaman karir situs Asthana: rincian lowongan (unit, jenis, lokasi, pola
 * kerja, batas lamaran, ringkasan, gaji, jumlah posisi) dan lamaran yang lebih
 * luwes (lamaran umum tanpa posisi, surat lamaran opsional, tautan portofolio,
 * sumber info).
 *
 * Semuanya TAMBAHAN: kolom baru boleh kosong / punya nilai bawaan, dan dua
 * kolom lamaran hanya dilonggarkan menjadi boleh NULL. Lowongan & lamaran lama
 * tetap utuh tanpa perlu diisi ulang.
 *
 * Kolom pilihan memakai string, bukan ENUM: menambah pilihan baru nanti cukup
 * lewat kode, tanpa ALTER di produksi.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lowongans', function (Blueprint $table) {
            $table->string('unit', 20)->default('asthana')->after('slug');
            $table->string('jenis', 20)->nullable()->after('unit');
            $table->string('lokasi', 100)->nullable()->after('jenis');
            $table->string('pola_kerja', 20)->nullable()->after('lokasi');
            $table->date('batas_lamaran')->nullable()->after('pola_kerja');
            $table->string('ringkasan', 255)->nullable()->after('batas_lamaran');
            $table->string('gaji', 100)->nullable()->after('ringkasan');
            $table->unsignedSmallInteger('jumlah_posisi')->nullable()->after('gaji');
        });

        Schema::table('tbl_job_applications', function (Blueprint $table) {
            // Lamaran umum (talent pool) tidak terikat ke satu lowongan.
            $table->unsignedBigInteger('job_id')->nullable()->change();
            $table->string('cover_letter_path')->nullable()->change();
            $table->string('portofolio_url', 255)->nullable()->after('cover_letter_path');
            $table->string('sumber_info', 50)->nullable()->after('portofolio_url');
        });
    }

    public function down(): void
    {
        // Hanya kolom tambahan yang dibuang. job_id & cover_letter_path sengaja
        // tidak dikembalikan ke NOT NULL: lamaran umum / tanpa surat yang
        // sudah masuk akan membuat ALTER itu gagal.
        Schema::table('tbl_job_applications', function (Blueprint $table) {
            $table->dropColumn(['portofolio_url', 'sumber_info']);
        });

        Schema::table('lowongans', function (Blueprint $table) {
            $table->dropColumn(['unit', 'jenis', 'lokasi', 'pola_kerja', 'batas_lamaran', 'ringkasan', 'gaji', 'jumlah_posisi']);
        });
    }
};
