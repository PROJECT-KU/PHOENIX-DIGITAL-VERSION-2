<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Isi situs profil PT. Asthana Cipta Mandiri yang dikelola dari lemon.
 *
 * Situsnya statis (Vite) dan tidak punya basis data sendiri; lowongan,
 * lamaran, dan pesannya sudah lebih dulu tinggal di Phoenix. Tabel-tabel ini
 * menyusul ke tempat yang sama supaya admin cukup membuka satu panel.
 *
 * Semuanya BARU — tidak ada tabel lama yang diubah.
 *
 * Kolom "tampil"/"terbit" sengaja ada di tiap tabel: isi yang sedang
 * disiapkan tidak boleh langsung muncul di situs publik.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Portofolio & berita: tulisan panjang dengan isi dari penyunting teks.
        Schema::create('asthana_portofolio', function (Blueprint $t) {
            $t->id();
            $t->string('judul');
            $t->string('slug')->unique();
            $t->string('kategori', 20)->index();            // teknologi | pariwisata
            $t->string('klien')->nullable();
            $t->unsignedSmallInteger('tahun')->nullable();
            $t->string('tautan')->nullable();                // alamat proyek yang bisa dikunjungi
            $t->text('ringkasan')->nullable();
            $t->longText('isi')->nullable();
            $t->string('sampul')->nullable();
            $t->json('galeri')->nullable();
            $t->boolean('unggulan')->default(false);         // tampil di beranda
            $t->boolean('terbit')->default(false)->index();
            $t->unsignedInteger('urutan')->default(0);
            $t->timestamps();
        });

        Schema::create('asthana_berita', function (Blueprint $t) {
            $t->id();
            $t->string('judul');
            $t->string('slug')->unique();
            $t->string('kategori', 60)->nullable();
            $t->text('ringkasan')->nullable();
            $t->longText('isi')->nullable();
            $t->string('sampul')->nullable();
            // Kosong = draf. Tanggal di masa depan = terjadwal.
            $t->timestamp('terbit_at')->nullable()->index();
            $t->timestamps();
        });

        // Tiga daftar pendek: isiannya sedikit, diurus lewat jendela.
        Schema::create('asthana_klien', function (Blueprint $t) {
            $t->id();
            $t->string('nama');
            $t->string('logo')->nullable();
            $t->string('tautan')->nullable();
            $t->boolean('tampil')->default(true);
            $t->unsignedInteger('urutan')->default(0);
            $t->timestamps();
        });

        Schema::create('asthana_testimoni', function (Blueprint $t) {
            $t->id();
            $t->string('nama');
            $t->string('jabatan')->nullable();
            $t->string('perusahaan')->nullable();
            $t->text('isi');
            $t->string('foto')->nullable();
            $t->unsignedTinyInteger('bintang')->default(5);
            $t->boolean('tampil')->default(true);
            $t->unsignedInteger('urutan')->default(0);
            $t->timestamps();
        });

        Schema::create('asthana_tim', function (Blueprint $t) {
            $t->id();
            $t->string('nama');
            $t->string('jabatan');
            $t->string('foto')->nullable();
            $t->string('linkedin')->nullable();
            $t->boolean('tampil')->default(true);
            $t->unsignedInteger('urutan')->default(0);
            $t->timestamps();
        });

    }

    public function down(): void
    {
        foreach (['asthana_tim', 'asthana_testimoni', 'asthana_klien', 'asthana_berita', 'asthana_portofolio'] as $tabel) {
            Schema::dropIfExists($tabel);
        }
    }
};
