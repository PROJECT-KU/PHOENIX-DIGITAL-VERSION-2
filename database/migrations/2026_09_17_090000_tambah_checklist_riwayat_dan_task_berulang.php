<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Empat penambahan pada layar Task Saya, semuanya ADITIF — tidak ada kolom
 * lama yang diubah artinya, jadi task yang sudah ada tetap berperilaku sama.
 *
 * 1. task_checklists  : sub-langkah di dalam satu task. Task berbobot "berat"
 *                       biasanya berisi beberapa langkah; sebelumnya satu-
 *                       satunya cara memecahnya adalah membuat beberapa task
 *                       terpisah — dan itu mengacaukan hitungan bobot & poin,
 *                       karena tiga task ringan bukan satu task berat.
 * 2. task_riwayats    : siapa mengubah apa, kapan. Yang tersimpan sebelumnya
 *                       cuma completed_at, padahal saat ada perselisihan bonus
 *                       yang ditanyakan justru urutan kejadiannya.
 * 3. tasks.ulang      : task berulang (mingguan/bulanan). Laporan berkala
 *                       sebelumnya dibuat ulang manual tiap periode.
 * 4. task_attachments.jenis : memisahkan berkas PERINTAH (dari pemberi) dari
 *                       berkas HASIL (dari penerima). Tanpa kolom ini keduanya
 *                       hanya bisa dibedakan dari pengunggahnya — dan itu
 *                       ambigu saat pemberi dan penerimanya orang yang sama.
 *
 * Semua kolom pilihan ditulis sebagai string biasa, BUKAN enum: enum harus
 * di-ALTER tiap kali ada nilai baru, dan pengujian berjalan di SQLite yang
 * tidak pernah menangkap kelalaian itu.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('task_checklists')) {
            Schema::create('task_checklists', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->uuid('task_id');
                $table->string('teks');
                $table->boolean('selesai')->default(false);
                $table->unsignedSmallInteger('urutan')->default(0);
                $table->timestamps();

                $table->index(['task_id', 'urutan']);
            });
        }

        if (! Schema::hasTable('task_riwayats')) {
            Schema::create('task_riwayats', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->uuid('task_id');
                // Pelakunya boleh kosong: perubahan yang dilakukan penjadwal
                // tidak punya pengguna, dan memaksanya ada akan membuat
                // riwayatnya bohong.
                $table->uuid('user_id')->nullable();
                $table->string('aksi');
                $table->string('dari')->nullable();
                $table->string('ke')->nullable();
                $table->text('catatan')->nullable();
                $table->timestamps();

                $table->index(['task_id', 'created_at']);
            });
        }

        Schema::table('tasks', function (Blueprint $table) {
            if (! Schema::hasColumn('tasks', 'ulang')) {
                // 'tidak' | 'mingguan' | 'bulanan'
                $table->string('ulang', 20)->default('tidak')->after('progress');
            }
            if (! Schema::hasColumn('tasks', 'ulang_terakhir_at')) {
                // Penanda anti-ganda: penjadwal berjalan tiap hari, dan tanpa
                // penanda ini satu task berulang akan disalin berkali-kali.
                $table->timestamp('ulang_terakhir_at')->nullable()->after('ulang');
            }
        });

        Schema::table('task_attachments', function (Blueprint $table) {
            if (! Schema::hasColumn('task_attachments', 'jenis')) {
                // 'perintah' = lampiran dari pemberi, 'hasil' = unggahan penerima.
                // Bawaannya 'perintah' supaya seluruh lampiran lama tetap
                // tampil di tempatnya semula.
                $table->string('jenis', 20)->default('perintah')->after('name');
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('task_checklists');
        Schema::dropIfExists('task_riwayats');

        Schema::table('tasks', function (Blueprint $table) {
            foreach (['ulang', 'ulang_terakhir_at'] as $kolom) {
                if (Schema::hasColumn('tasks', $kolom)) {
                    $table->dropColumn($kolom);
                }
            }
        });

        Schema::table('task_attachments', function (Blueprint $table) {
            if (Schema::hasColumn('task_attachments', 'jenis')) {
                $table->dropColumn('jenis');
            }
        });
    }
};
