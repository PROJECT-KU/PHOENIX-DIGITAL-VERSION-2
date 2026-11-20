<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Arsip Surat — buku agenda surat masuk & keluar.
 *
 * Empat tabel, dan pembagiannya mengikuti cara kerja administrasi yang sudah
 * ada di kertas, bukan cara kerja program:
 *
 *   surat_klasifikasi  daftar kode klasifikasi (kualifikasi surat). Master
 *                      data, diisi sekali lalu dipakai terus.
 *   surat             satu baris = satu surat di buku agenda.
 *   surat_lampiran    hasil pindai berkas fisiknya; satu surat bisa berlembar.
 *   surat_disposisi   perintah tindak lanjut dari atasan ke pelaksana.
 *
 * Kuncinya angka berurut, bukan UUID: nomor agenda yang dibaca manusia
 * memang berurut, dan admin yang awam akan menyebut "surat nomor 12", bukan
 * deretan 36 huruf.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('surat_klasifikasi', function (Blueprint $t) {
            $t->id();
            $t->string('kode', 40)->unique();
            $t->string('nama', 150);
            $t->string('keterangan', 255)->nullable();

            /*
             | Masa simpan dalam TAHUN, boleh kosong.
             |
             | Kosong berarti "belum ditentukan", bukan "nol tahun" — dan
             | perbedaan itu penting: nol tahun berarti boleh dimusnahkan hari
             | ini. Dari sini tanggal musnah tiap surat dihitung sendiri.
             */
            $t->unsignedSmallInteger('retensi_tahun')->nullable();
            $t->boolean('aktif')->default(true);
            $t->unsignedInteger('urutan')->default(0);
            $t->timestamps();
        });

        Schema::create('surat', function (Blueprint $t) {
            $t->id();

            // 'masuk' | 'keluar'. Penentu hampir seluruh tampilan: surat masuk
            // bicara soal "dari siapa", surat keluar soal "untuk siapa".
            $t->string('arah', 10)->index();

            /*
             | Nomor agenda — nomor ARSIP, dibuat sendiri oleh sistem.
             |
             | Berbeda dari nomor_surat, yang ditulis oleh pembuat suratnya.
             | Surat masuk dari luar sering bernomor seragam, kosong, atau
             | kembar; tanpa nomor milik sendiri, satu-satunya cara menunjuk
             | sebuah surat adalah perihalnya — dan perihal tidak pernah unik.
             */
            $t->string('nomor_agenda', 40)->unique();
            $t->string('nomor_surat', 120)->nullable();

            $t->date('tanggal_surat');

            /*
             | Tanggal surat DITERIMA (masuk) atau DIKIRIM (keluar).
             |
             | Satu kolom untuk dua arti, karena keduanya menjawab pertanyaan
             | yang sama: kapan surat ini melintasi meja kita. Dua kolom
             | terpisah akan selalu menyisakan satu yang kosong, dan laporan
             | harus memilih kolom menurut arah di tiap barisnya.
             */
            $t->date('tanggal_catat')->nullable();

            $t->foreignId('klasifikasi_id')->nullable()
                ->constrained('surat_klasifikasi')->nullOnDelete();

            // 'biasa' | 'segera' | 'sangat_segera' | 'rahasia'
            $t->string('sifat', 20)->default('biasa')->index();

            $t->string('perihal', 255);
            $t->string('dari', 180)->nullable();
            $t->string('kepada', 180)->nullable();

            // 'baru' | 'diproses' | 'selesai' | 'diarsipkan'
            $t->string('status', 20)->default('baru')->index();

            // Di mana kertasnya disimpan: "Lemari 2, Boks B, Berkas 14".
            // Satu kotak teks bebas, bukan tiga kolom — tiap kantor menyusun
            // lemarinya sendiri, dan kolom yang tidak cocok akan diisi tanda
            // hubung lalu berhenti dipercaya.
            $t->string('lokasi_fisik', 180)->nullable();

            $t->text('ringkasan')->nullable();

            // Tanggal boleh musnah, dihitung dari retensi klasifikasinya saat
            // disimpan. Disimpan, bukan dihitung saat dibaca: retensi bisa
            // diubah kemudian, dan surat yang sudah diarsipkan tidak boleh
            // berubah masa simpannya diam-diam.
            $t->date('musnah_pada')->nullable();

            $t->foreignId('dicatat_oleh')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamps();
            $t->softDeletes();

            // Dua pencarian yang paling sering: buku agenda per arah per
            // tanggal, dan "surat mana yang belum selesai".
            $t->index(['arah', 'tanggal_surat']);
            $t->index(['arah', 'status']);
        });

        Schema::create('surat_lampiran', function (Blueprint $t) {
            $t->id();
            $t->foreignId('surat_id')->constrained('surat')->cascadeOnDelete();
            $t->string('nama', 255);

            // Jalur di disk PRIVAT (storage/app). Hasil pindai surat bisa
            // memuat nama, alamat, dan nomor telepon orang; disk publik
            // berarti siapa pun yang tahu URL-nya bisa mengunduhnya selamanya
            // tanpa login. Disajikan lewat BerkasPrivatController.
            $t->string('jalur', 255);
            $t->unsignedBigInteger('ukuran')->nullable();
            $t->string('jenis', 100)->nullable();
            $t->foreignId('diunggah_oleh')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamps();
        });

        Schema::create('surat_disposisi', function (Blueprint $t) {
            $t->id();
            $t->foreignId('surat_id')->constrained('surat')->cascadeOnDelete();

            // Tujuannya bisa akun lemon ATAU nama yang ditulis tangan: tidak
            // semua orang yang didisposisi punya akun, dan menolak nama tanpa
            // akun berarti disposisinya tidak tercatat sama sekali.
            $t->foreignId('kepada_user_id')->nullable()->constrained('users')->nullOnDelete();
            $t->string('kepada_nama', 180)->nullable();

            $t->text('instruksi');
            $t->date('batas')->nullable();

            // 'menunggu' | 'dikerjakan' | 'selesai'
            $t->string('status', 20)->default('menunggu')->index();
            $t->timestamp('selesai_pada')->nullable();
            $t->foreignId('dibuat_oleh')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('surat_disposisi');
        Schema::dropIfExists('surat_lampiran');
        Schema::dropIfExists('surat');
        Schema::dropIfExists('surat_klasifikasi');
    }
};
