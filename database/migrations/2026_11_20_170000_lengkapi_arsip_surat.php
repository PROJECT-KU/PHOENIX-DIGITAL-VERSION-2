<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Melengkapi arsip surat: kaitan antar surat, tembusan, jejak perubahan,
 * dan berita acara pemusnahan.
 *
 * Empat hal yang di buku agenda kertas sudah ada sejak dulu, dan hanya
 * terasa hilangnya setelah arsipnya tebal:
 *
 *   induk_id        surat ini menanggapi surat yang mana. Satu urusan yang
 *                   berjalan lima surat perlu benang merah; tanpa itu
 *                   riwayatnya tersebar di lima baris yang tidak saling tahu.
 *   tembusan        daftar pihak yang ditembusi. Di surat resmi ia tercetak
 *                   di kaki surat, dan sering jadi satu-satunya bukti bahwa
 *                   pihak ketiga memang sudah diberi tahu.
 *   surat_jejak     siapa mengubah apa. Untuk catatan resmi, pertanyaan itu
 *                   cepat atau lambat ditanyakan — dan jawabannya tidak bisa
 *                   dibuat surut.
 *   surat_pemusnahan  berita acara pemusnahan. Masa simpan dicatat justru
 *                   supaya suatu hari arsipnya bisa dimusnahkan dengan sah;
 *                   tanpa berita acaranya, penandaan retensi berhenti jadi
 *                   peringatan yang tidak pernah bisa ditutup.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('surat_pemusnahan', function (Blueprint $t) {
            $t->id();

            // BA-2026-001. Dibuat sistem, berurut per tahun.
            $t->string('nomor', 40)->unique();
            $t->date('tanggal');
            $t->string('penanggung_jawab', 180)->nullable();
            $t->text('catatan')->nullable();
            $t->foreignId('dibuat_oleh')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamps();
        });

        Schema::create('surat_jejak', function (Blueprint $t) {
            $t->id();
            $t->foreignId('surat_id')->constrained('surat')->cascadeOnDelete();

            // 'dicatat' | 'diubah' | 'status' | 'disposisi' | 'lampiran' | 'dihapus' | 'dipulihkan' | 'dimusnahkan'
            $t->string('aksi', 30);
            $t->text('keterangan')->nullable();
            $t->foreignId('oleh')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamps();

            /*
             | Diurutkan dengan id, bukan created_at saja.
             |
             | Beberapa jejak lahir dalam detik yang sama (ubah data + ubah
             | status), dan urutan baris yang nilainya seri tidak dijamin —
             | riwayat yang terbaca terbalik lebih buruk daripada tidak ada.
             */
            $t->index(['surat_id', 'id'], 'surat_jejak_surat_idx');
        });

        Schema::table('surat', function (Blueprint $t) {
            $t->foreignId('induk_id')->nullable()->after('arah')
                ->constrained('surat')->nullOnDelete();
            $t->text('tembusan')->nullable()->after('kepada');
            $t->foreignId('pemusnahan_id')->nullable()->after('musnah_pada')
                ->constrained('surat_pemusnahan')->nullOnDelete();
        });

        Schema::table('surat_disposisi', function (Blueprint $t) {
            /*
             | Penanda "sudah diberi tahu", bukan penghitung.
             |
             | Pengingat tenggat berjalan tiap hari; tanpa penanda, satu
             | disposisi yang telat seminggu akan mengirim tujuh notifikasi
             | yang sama dan orangnya berhenti membacanya.
             */
            $t->timestamp('diberitahu_pada')->nullable()->after('status');
            $t->timestamp('telat_diberitahu_pada')->nullable()->after('diberitahu_pada');
        });
    }

    public function down(): void
    {
        Schema::table('surat_disposisi', function (Blueprint $t) {
            $t->dropColumn(['diberitahu_pada', 'telat_diberitahu_pada']);
        });

        Schema::table('surat', function (Blueprint $t) {
            $t->dropConstrainedForeignId('induk_id');
            $t->dropConstrainedForeignId('pemusnahan_id');
            $t->dropColumn('tembusan');
        });

        Schema::dropIfExists('surat_jejak');
        Schema::dropIfExists('surat_pemusnahan');
    }
};
