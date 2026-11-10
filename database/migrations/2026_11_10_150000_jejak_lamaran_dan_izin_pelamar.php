<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Pelamar tahap 3 — tiga kekurangan yang semuanya soal pertanggungjawaban.
 *
 * 1. JEJAK. Sebelum ini yang tersimpan cuma status terakhir dan satu stempel
 *    waktu: tidak ada yang tahu SIAPA yang menolak seorang pelamar, atau dari
 *    tahap mana. Catatan Tim pun satu kotak teks — dua orang menulis, yang
 *    kedua menimpa yang pertama tanpa jejak. Sekarang keduanya jadi baris
 *    jejak bertanda penulis dan waktu.
 *
 * 2. BERKAS DIBUKA. Daftar pelamar menulis "belum ada yang membuka berkasnya"
 *    padahal tidak ada apa pun yang mencatatnya — kalimat itu cuma menebak
 *    dari status 'baru'. Sekarang ada kolomnya, diisi saat berkasnya benar-
 *    benar disajikan ke peramban.
 *
 * 3. IZIN edit_pelamar. Selama ini siapa pun yang boleh MELIHAT pelamar juga
 *    bisa mengubah tahap seleksi dan memicu surel ke pelamar — padahal surel
 *    yang sudah keluar tidak bisa ditarik. Izinnya dibuat di sini.
 *
 *    Izin baru TIDAK otomatis sampai ke peran yang sudah ada, dan peran di
 *    produksi bukan bernama 'admin' seperti di seeder. Maka izin ini langsung
 *    dilekatkan ke SETIAP peran yang hari ini sudah punya view_pelamar: tanpa
 *    itu, deploy ini diam-diam mencabut kemampuan semua orang mengubah tahap.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tbl_jejak_lamaran', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('job_application_id')->index();
            // Penulisnya boleh kosong: baris hasil pemindahan catatan lama
            // tidak punya penulis, dan akun yang dihapus tidak boleh ikut
            // menghapus jejaknya.
            $t->unsignedBigInteger('user_id')->nullable();
            $t->string('nama_penulis')->nullable();
            // 'tahap' | 'catatan' | 'surel'
            $t->string('jenis', 20)->index();
            $t->string('dari_status', 20)->nullable();
            $t->string('ke_status', 20)->nullable();
            $t->text('isi')->nullable();
            $t->timestamps();
        });

        Schema::table('tbl_job_applications', function (Blueprint $t) {
            $t->timestamp('berkas_dibuka_at')->nullable()->after('status_diubah_at');
            $t->string('berkas_dibuka_oleh')->nullable()->after('berkas_dibuka_at');
        });

        $this->pindahkanCatatanLama();
        $this->buatIzinEdit();
    }

    /**
     * Catatan internal lama jadi baris jejak pertama.
     *
     * Kolomnya TIDAK dihapus: kalau migrasi ini harus dibatalkan, catatannya
     * harus masih ada di tempat semula. Yang membaca catatan sesudah ini
     * adalah tabel jejak.
     */
    private function pindahkanCatatanLama(): void
    {
        DB::table('tbl_job_applications')
            ->whereNotNull('catatan_internal')
            ->where('catatan_internal', '<>', '')
            ->orderBy('id')
            ->each(function ($baris) {
                DB::table('tbl_jejak_lamaran')->insert([
                    'job_application_id' => $baris->id,
                    'user_id' => null,
                    'nama_penulis' => null,
                    'jenis' => 'catatan',
                    'isi' => $baris->catatan_internal,
                    // Waktu aslinya tidak pernah dicatat; tanggal lamaran
                    // adalah tebakan paling jujur yang kita punya, dan
                    // nama_penulis yang kosong menandai bahwa ini catatan
                    // pindahan, bukan tulisan seseorang hari ini.
                    'created_at' => $baris->updated_at ?? $baris->created_at,
                    'updated_at' => $baris->updated_at ?? $baris->created_at,
                ]);
            });
    }

    private function buatIzinEdit(): void
    {
        if (! Schema::hasTable('permissions') || ! Schema::hasTable('role_permission')) {
            return;
        }

        $id = DB::table('permissions')->where('name', 'edit_pelamar')->value('id');

        if (! $id) {
            $id = DB::table('permissions')->insertGetId([
                'name' => 'edit_pelamar',
                'display_name' => 'Edit Pelamar',
                'group' => 'pelamar',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $lihat = DB::table('permissions')->where('name', 'view_pelamar')->value('id');

        if (! $lihat) {
            return;
        }

        $peran = DB::table('role_permission')->where('permission_id', $lihat)->pluck('role_id');

        foreach ($peran as $roleId) {
            $sudah = DB::table('role_permission')
                ->where('role_id', $roleId)->where('permission_id', $id)->exists();

            if (! $sudah) {
                DB::table('role_permission')->insert([
                    'role_id' => $roleId,
                    'permission_id' => $id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // Izin per-pengguna (di luar peran) diperlakukan sama.
        if (Schema::hasTable('permission_user')) {
            $pengguna = DB::table('permission_user')->where('permission_id', $lihat)->pluck('user_id');

            foreach ($pengguna as $userId) {
                $sudah = DB::table('permission_user')
                    ->where('user_id', $userId)->where('permission_id', $id)->exists();

                if (! $sudah) {
                    DB::table('permission_user')->insert([
                        'user_id' => $userId,
                        'permission_id' => $id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_jejak_lamaran');

        Schema::table('tbl_job_applications', function (Blueprint $t) {
            $t->dropColumn(['berkas_dibuka_at', 'berkas_dibuka_oleh']);
        });

        if (Schema::hasTable('permissions')) {
            $id = DB::table('permissions')->where('name', 'edit_pelamar')->value('id');

            if ($id) {
                DB::table('role_permission')->where('permission_id', $id)->delete();
                if (Schema::hasTable('permission_user')) {
                    DB::table('permission_user')->where('permission_id', $id)->delete();
                }
                DB::table('permissions')->where('id', $id)->delete();
            }
        }
    }
};
